<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Class Comment
 * 
 * Handles all logic for fetching, submitting, and liking comments on posts.
 * Primarily interacts with AJAX fetch requests returning JSON data.
 */
class Comment extends Controller
{
    /**
     * Fetch all comments for a specific post.
     * Route: GET /api/comments/(:num)
     * 
     * Debugging Note: This executes a complex Raw SQL string to simultaneously fetch the comment, the user's data who posted it,
     * the total likes on the comment, and whether the currently logged-in user has already liked it.
     * If comments fail to load, check the browser Network tab for exact Database SQL errors generated here.
     *
     * @param int $post_id The ID of the post to fetch comments for.
     * @return \CodeIgniter\HTTP\Response JSON containing 'comments' array.
     */
    public function fetch($post_id)
    {
        $db = \Config\Database::connect();
        $user_id = session()->get('user_id') ?? 0;
        
        $comments = $db->query("
            SELECT comments.*, users.username, users.profile_pic,
            (SELECT COUNT(*) FROM comment_likes WHERE comment_likes.comment_id = comments.id) as like_count,
            (SELECT COUNT(*) FROM comment_likes WHERE comment_likes.comment_id = comments.id AND comment_likes.user_id = ?) as has_liked
            FROM comments 
            JOIN users ON users.id = comments.user_id
            WHERE post_id = ?
            ORDER BY comments.created_at DESC
        ", [$user_id, $post_id])->getResultArray();

        return $this->response->setJSON(['success' => true, 'comments' => $comments]);
    }

    /**
     * Store a new comment into the database.
     * Route: POST /comment/store
     * 
     * Requires: `post_id` and `comment` (text).
     * Security: Rejects empty comments and forces authentication.
     *
     * @return \CodeIgniter\HTTP\Response JSON representing the individual new comment instance for Javascript rendering.
     */
    public function store()
    {
        $db = \Config\Database::connect();

        // 1. Session Authorization Boundary
        $user_id = session()->get('user_id');
        if (!$user_id) {
            return $this->response->setJSON(['success' => false, 'redirect' => '/login']);
        }

        // 2. Data Retrieval
        $post_id = $this->request->getPost('post_id');
        $comment = $this->request->getPost('comment');

        // 3. Validation Logic
        if (empty(trim($comment))) {
            return $this->response->setJSON(['success' => false, 'error' => 'Comment cannot be empty']);
        }

        // 4. Database Insertion
        $db->table('comments')->insert([
            'user_id' => $user_id,
            'post_id' => $post_id,
            'comment' => $comment
        ]);

        // 5. Live Rendering Object
        // Fetch the user data again so Javascript can append the comment visually instantly without reloading
        $user = $db->table('users')->where('id', $user_id)->get()->getRow();

        return $this->response->setJSON([
            'success' => true,
            'comment' => [
                'id'          => $db->insertID(),
                'username'    => $user->username,
                'profile_pic' => $user->profile_pic,
                'comment'     => $comment,
                'created_at'  => date('Y-m-d H:i:s'),
                'like_count'  => 0,
                'has_liked'   => false
            ]
        ]);
    }

    /**
     * Toggles a 'Like' status on a specific comment.
     * Route: POST /comment/like/toggle
     * 
     * Expects: `comment_id`.
     * Logic: If a like exists, DECREMENT it. If NO like exists, INCREMENT it.
     *
     * @return \CodeIgniter\HTTP\Response JSON indicating the new like integer constraint and boolean status.
     */
    public function toggleLike()
    {
        $db = \Config\Database::connect();
        $user_id = session()->get('user_id');
        $comment_id = $this->request->getPost('comment_id');

        // Block anonymous API interaction
        if(!$user_id) return $this->response->setJSON(['success' => false]);

        $comment = $db->table('comments')->where('id', $comment_id)->get()->getRow();

        // 1. Database Check (Does Like Exist?)
        $existing = $db->table('comment_likes')
            ->where('user_id', $user_id)
            ->where('comment_id', $comment_id)
            ->get()->getRow();

        $likedStatus = false;
        
        if ($existing) {
            // Case A: User already liked it. Remove the row (Unlike).
            $db->table('comment_likes')->where('id', $existing->id)->delete();
        } else {
            // Case B: User hasn't liked it. Insert a new row (Like).
            $db->table('comment_likes')->insert([
                'user_id' => $user_id,
                'comment_id' => $comment_id
            ]);
            $likedStatus = true;

        }

        // 2. Tally recalculation to send valid numeric data back to frontend
        $count = $db->table('comment_likes')->where('comment_id', $comment_id)->countAllResults();
        
        return $this->response->setJSON([
            'success'   => true, 
            'like_count'=> $count, 
            'liked'     => $likedStatus
        ]);
    }
}
