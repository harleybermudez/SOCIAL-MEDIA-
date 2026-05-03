<?php

namespace App\Controllers;

/**
 * Class Profile
 * 
 * Handles rendering User Profiles, generating their personal media grid,
 * checking relationship statuses (friends vs strangers), and editing their public Bio/Avatar.
 */
class Profile extends BaseController
{
    /**
     * Renders a specific user's public profile page.
     * Route: GET /profile/(:num)
     *
     * @param int $id The specific user's database ID.
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function index($id)
    {
        $db = \Config\Database::connect();

        // 1. Target User Identity
        $user = $db->table('users')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        // Bail out silently if trying to load a deleted or fake ID
        if (!$user) {
            return redirect()->to('/feed');
        }

        // 2. Fetch User's Personal Media Grid
        $posts = $db->table('posts')
            ->select('posts.*, users.username, users.profile_pic')
            ->join('users', 'users.id = posts.user_id', 'inner')
            ->where('posts.user_id', $id)
            ->orderBy('posts.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $currentUser = session()->get('user_id');

        /**
         * 3. Relationship Matrix Calculator
         * Extremely important: Is the person viewing this page a friend of the profile owner?
         * Check both mathematical possibilities using Query Builder group logic.
         */
        $friendship = $db->table('friends')
            ->groupStart()
                ->where('sender_id', $currentUser)
                ->where('receiver_id', $id)
            ->groupEnd()
            ->orGroupStart()
                ->where('sender_id', $id)
                ->where('receiver_id', $currentUser)
            ->groupEnd()
            ->get()
            ->getRowArray();

        /**
         * 4. Friend Tally
         * How many active friends does this user have? (Only counts accepted requests).
         */
        $friendCount = $db->query("
            SELECT COUNT(*) as count
            FROM friends
            WHERE (sender_id = ? OR receiver_id = ?)
            AND status = 'accepted'
        ", [$id, $id])->getRow()->count;

        // 5. Package for View Execution
        $data = [
            'user' => $user,
            'posts' => $posts,
            'friendship' => $friendship,
            'friendCount' => $friendCount
        ];

        return view('profile/view', $data);
    }

    /**
     * Display the Edit Profile modal UI.
     * Route: GET /profile/edit
     *
     * @return string
     */
    public function edit()
    {
        $db = \Config\Database::connect();

        $user = $db->table('users')
            ->where('id', session()->get('user_id'))
            ->get()
            ->getRowArray();

        return view('profile/edit', ['user' => $user]);
    }

    /**
     * Executes logic to overwrite a user's Bio and Avatar.
     * Route: POST /profile/update
     * 
     * Handles specific collision bugs like overwriting an avatar with NULL if they only updated their bio.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function update()
    {
        $db = \Config\Database::connect();

        // 1. Boundary Lock
        $user_id = session()->get('user_id');
        $user = $db->table('users')->where('id', $user_id)->get()->getRowArray();

        $file = $this->request->getFile('profile_pic');
        
        // Default to keeping the exact same profile picture if they didn't upload a new one
        $picName = $user['profile_pic'] ?? null;

        // 2. Avatar Replacement Engine
        if ($file && $file->isValid() && !$file->hasMoved()) {
            
            // Re-name the file to a completely stripped string
            $picName = $file->getRandomName();
            $file->move('uploads/profile_pics', $picName);
            
            /** 
             * NATIVE IMAGE COMPRESSION BLOCK
             * Scrapes 40% of the image weight arbitrarily and scales down rigidly.
             */
            try {
                \Config\Services::image()
                    ->withFile('uploads/profile_pics/' . $picName)
                    ->resize(250, 250, true, 'auto')
                    ->save('uploads/profile_pics/' . $picName, 60);
            } catch (\Exception $e) {}
        }

        // 3. Database Overwrite
        $db->table('users')
            ->where('id', $user_id)
            ->update([
                'username'    => $this->request->getPost('username'),
                'bio'         => $this->request->getPost('bio'),
                'profile_pic' => $picName
            ]);

        // Redirect identically back to the profile viewer natively
        return redirect()->to('/profile/' . $user_id);
    }
}