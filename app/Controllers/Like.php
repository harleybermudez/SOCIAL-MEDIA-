<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Class Like
 * 
 * Manages the logic for toggling "Likes" on Posts via asynchronous JSON API requests.
 */
class Like extends Controller
{
    /**
     * Toggles the like status of a specific post.
     * Route: POST /like/toggle
     * 
     * Expects: `post_id` (via POST block).
     * Behavior: Dynamically checks database state. If the user hasn't liked it, inserts a row.
     * If they already liked it, it deletes the row. It never reloads the DOM natively.
     *
     * @return \CodeIgniter\HTTP\Response Dynamic JSON packet indicating total like counts and boolean state.
     */
    public function toggle()
    {
        $db = \Config\Database::connect();

        // 1. Establish Current Identity
        $user_id = session()->get('user_id');
        $post_id = $this->request->getPost('post_id');

        // 2. Look for existing like interaction row
        $existing = $db->table('likes')
            ->where('user_id', $user_id)
            ->where('post_id', $post_id)
            ->get()
            ->getRow();

        $likedStatus = false;
        
        // 3. State Decision Matrix
        if ($existing) {
            // They already liked it. Execution path: Dislike (Delete Database Row)
            $db->table('likes')->where('id', $existing->id)->delete();
            $likedStatus = false;
        } else {
            // They did not like it yet. Execution path: Like (Insert Database Row)
            $db->table('likes')->insert([
                'user_id' => $user_id,
                'post_id' => $post_id
            ]);
            $likedStatus = true;

        }

        // 4. Final Tally Recalculation
        // Recounts EXACT rows natively to provide extreme accuracy against spam-clicks instead of relying on $var++
        $count = $db->table('likes')->where('post_id', $post_id)->countAllResults();
        
        return $this->response->setJSON([
            'success' => true, 
            'like_count' => $count, 
            'liked' => $likedStatus
        ]);
    }
}
