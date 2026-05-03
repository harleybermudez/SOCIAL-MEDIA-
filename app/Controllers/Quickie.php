<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Class Quickie
 * 
 * Handles the immersive, full-screen vertical swipe feed (similar to TikTok or Instagram Reels).
 * Responsible for gathering specially flagged posts (is_quickie = 1) from the database 
 * alongside all their relational user data.
 */
class Quickie extends Controller
{
    /**
     * Primary endpoint for the Quickie feed.
     * Route: GET /quickie
     * 
     * Security: Forces authentication boundary.
     * Logic: Executes a heavy JOIN Query Builder pattern to assemble Post data, User profile data,
     * and active viewer Like metrics into a single array passed to the View.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string The quickie view or a redirect to login.
     */
    public function index()
    {
        // 1. Session Boundary Enforcement
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $userId = session()->get('user_id') ?? 0;

        /**
         * 2. Massive Data Assembly
         * 
         * Debugging Note: If Quickie feed is blank, verify that the `is_quickie` column exists 
         * in the `posts` table and is set to 1 for media posts.
         * 
         * Select constraints:
         * - posts.* : Grabs the media file, caption, and timestamps
         * - users.username/profile_pic : Grabs the author's avatar efficiently
         * - like_count Subquery : Tallies total database rows for this post ID dynamically
         * - has_liked Subquery : Checks mathematically if the current viewer's ID is in the likes table
         */
        $data['quickies'] = $db->table('posts')
            ->select("posts.*, users.username, users.profile_pic, 
                     (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) as like_count, 
                     (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = $userId) as has_liked")
            ->join('users', 'users.id = posts.user_id')
            ->where('posts.is_quickie', 1)
            ->orderBy('posts.id', 'DESC')
            ->get()
            ->getResultArray();

        // 3. Render the immersive Vue/HTML Template
        return view('quickie', $data);
    }
}
