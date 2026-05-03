<?php

namespace App\Controllers;

/**
 * Class Friend
 * 
 * Handles all logic pertaining to the Friend system.
 * Manages Friend Requests (Accept/Reject) and the 
 * complex bi-directional querying needed to build a friend list regardless of who sent the request.
 */
class Friend extends BaseController
{
    /**
     * Renders the current user's friends and incoming friend requests.
     * Route: GET /friends
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function friends()
    {
        $db = \Config\Database::connect();
        $user_id = session()->get('user_id');

        if (!$user_id) {
            return redirect()->to('/login');
        }

        $friends = $db->query("
            SELECT 
                f.id as friendship_id,
                u.id,
                u.username,
                u.profile_pic
            FROM friends f
            JOIN users u ON (
                (f.sender_id = ? AND f.receiver_id = u.id) OR
                (f.receiver_id = ? AND f.sender_id = u.id)
            )
            WHERE (f.sender_id = ? OR f.receiver_id = ?)
            AND f.status = 'accepted'
            ORDER BY u.username
        ", [$user_id, $user_id, $user_id, $user_id])->getResultArray();

        $requests = $db->query("
            SELECT
                f.id as friendship_id,
                f.sender_id,
                u.username,
                u.profile_pic,
                f.created_at
            FROM friends f
            JOIN users u ON u.id = f.sender_id
            WHERE f.receiver_id = ?
            AND f.status = 'pending'
            ORDER BY f.created_at DESC
        ", [$user_id])->getResultArray();

        return view('friends/index', [
            'friends' => $friends,
            'requests' => $requests
        ]);
    }

    /**
     * Renders a base list of all registered users (excluding oneself).
     * Route: GET /users
     *
     * @return string
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $data['users'] = $db->table('users')
            ->where('id !=', session()->get('user_id'))
            ->get()->getResultArray();

        return view('users/list', $data);
    }

    /**
     * Sends a new friend request.
     * Route: POST /friend/request
     * 
     * Expects: `receiver_id` via POST.
     * Behavior: Initializes a 'pending' state row in the DB.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function request()
    {
        $db = \Config\Database::connect();

        $receiver_id = $this->request->getPost('receiver_id');
        $sender_id = session()->get('user_id');

        // 1. Insert friend request natively
        $db->table('friends')->insert([
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'status' => 'pending' // Default holding state
        ]);

        return redirect()->back();
    }

    /**
     * Direct fallback method to accept a friend request globally.
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function accept()
    {
        $db = \Config\Database::connect();
        $user_id = session()->get('user_id');
        $friendship_id = $this->request->getPost('id');

        if (!$user_id || !$friendship_id) {
            return redirect()->back();
        }

        $db->table('friends')
            ->where('id', $friendship_id)
            ->where('receiver_id', $user_id)
            ->where('status', 'pending')
            ->update(['status' => 'accepted']);

        return redirect()->back();
    }

    /**
     * Direct fallback method to completely unfriend / remove a friendship dynamically.
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function remove()
    {
        $db = \Config\Database::connect();
        $user_id = session()->get('user_id');
        $friendship_id = $this->request->getPost('id');

        if (!$user_id || !$friendship_id) {
            return redirect()->back();
        }

        $db->table('friends')
            ->where('id', $friendship_id)
            ->groupStart()
                ->where('sender_id', $user_id)
                ->orWhere('receiver_id', $user_id)
            ->groupEnd()
            ->delete();

        return redirect()->back();
    }

    /**
     * Rejects an incoming friend request from a standard form post.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function reject()
    {
        $db = \Config\Database::connect();
        $user_id = session()->get('user_id');
        $friendship_id = $this->request->getPost('id');

        if (!$user_id || !$friendship_id) {
            return redirect()->back();
        }

        $db->table('friends')
            ->where('id', $friendship_id)
            ->where('receiver_id', $user_id)
            ->where('status', 'pending')
            ->delete();

        return redirect()->back();
    }


    /**
     * JSON API Endpoint to fetch an array of all active friends.
     * Route: GET /api/friends/(:num)
     * 
     * Handles complex bi-directional logic: If "Bob" adds "Eve", Bob is sender. 
     * If "Eve" views her friends, the system must recognize she is receiver but they are both friends.
     *
     * @param int $user_id The targeted users ID 
     * @return \CodeIgniter\HTTP\Response JSON mapped friends list
     */
    public function getFriends($user_id)
    {
        $db = \Config\Database::connect();

        /**
         * COMPLEX SQL JOIN EXPLANATION:
         * We need to join the 'users' table to grab avatars.
         * We enforce the JOIN via an OR constraint:
         * Path A: The user sent the request. Result -> Grab the receiver's user profile.
         * Path B: The user received the request. Result -> Grab the sender's user profile.
         */
        $sql = "SELECT 
                    f.id as friendship_id,
                    u.id, u.username, u.profile_pic
                FROM friends f
                JOIN users u ON (
                    (f.sender_id = ? AND f.receiver_id = u.id) OR 
                    (f.receiver_id = ? AND f.sender_id = u.id)
                )
                WHERE (f.sender_id = ? OR f.receiver_id = ?)
                AND f.status = 'accepted'
                ORDER BY u.username";
        
        $friends = $db->query($sql, [$user_id, $user_id, $user_id, $user_id])->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'friends' => $friends
        ]);
    }

    /**
     * JSON API Endpoint to silently delete a friendship without page refresh.
     * Used securely by the Frontend JS.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function removeFriendApi()
    {
        $db = \Config\Database::connect();
        $user_id = session()->get('user_id');
        $friendship_id = $this->request->getPost('friendship_id');

        if (!$user_id || !$friendship_id) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        // 1. Locate relational boundary
        $friendship = $db->table('friends')
            ->where('id', $friendship_id)
            ->get()
            ->getRow();

        if (!$friendship) {
            return $this->response->setJSON(['success' => false, 'error' => 'Friendship not found']);
        }

        // 2. Strict Security Constraint
        // Prevent malicious users from deleting friendships they are not a part of.
        if ($friendship->sender_id != $user_id && $friendship->receiver_id != $user_id) {
            return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized']);
        }

        // 3. Execution
        $db->table('friends')->where('id', $friendship_id)->delete();

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * API Hook: Accept Friend Request via JSON payload.
     * 
     * Required POST Data: `sender_id`.
     */
    public function acceptRequest()
    {
        $db = \Config\Database::connect();
        $user_id = session()->get('user_id');
        $sender_id = $this->request->getPost('sender_id'); // This is the person who requested

        if (!$user_id || !$sender_id) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        // Identify exact mathematical relationship where status is specifically 'pending'
        $friendship = $db->table('friends')
            ->where('sender_id', $sender_id)
            ->where('receiver_id', $user_id)
            ->where('status', 'pending')
            ->get()
            ->getRow();

        if (!$friendship) {
            return $this->response->setJSON(['success' => false, 'error' => 'Friend request not found']);
        }

        // Solidify Friendship row
        $db->table('friends')
            ->where('id', $friendship->id)
            ->update(['status' => 'accepted']);

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * API Hook: Reject Friend Request via JSON payload.
     */
    public function rejectRequest()
    {
        $db = \Config\Database::connect();
        $user_id = session()->get('user_id');
        $sender_id = $this->request->getPost('sender_id');

        if (!$user_id || !$sender_id) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        // Identify specific mathematical relationship explicitly
        $friendship = $db->table('friends')
            ->where('sender_id', $sender_id)
            ->where('receiver_id', $user_id)
            ->where('status', 'pending')
            ->get()
            ->getRow();

        if (!$friendship) {
            return $this->response->setJSON(['success' => false, 'error' => 'Friend request not found']);
        }

        // Delete the friendship trace completely instead of maintaining a "rejected" row
        $db->table('friends')->where('id', $friendship->id)->delete();

        return $this->response->setJSON(['success' => true]);
    }
}
