<?php
/**
 * =========================================================
 *        UNYUN - APPLICATION DOCUMENTATION & LOGIC
 * =========================================================
 * Framework: CodeIgniter 4
 * Stack: PHP, MySQL, JavaScript (Vanilla API), Tailwind CSS
 * Environment: Laragon, Ngrok Compatible
 * 
 * This file serves as the literal master documentation for how 
 * the Unyun application operates, covering code logic, interactions,
 * and responsive styling.
 * 
 * =========================================================
 * 1. TAILWIND CSS & RESPONSIVE DESIGN ARCHITECTURE
 * =========================================================
 * How Tailwind works in this App:
 * Instead of having massive generic CSS files, Unyun uses Tailwind's utility classes. 
 * Tailwind relies on a "Mobile First" approach. This means default classes apply to phones, 
 * and classes with the `md:` prefix apply ONLY to tablets/desktop screens.
 * 
 * Example from `app/Views/layout/main.php` (The Global Navigation Bar):
 * 
 *      <nav class="bg-white border-t md:border-r md:border-t-0 border-gray-200 
 *                  fixed bottom-0 md:relative w-full md:w-64 md:h-screen md:flex md:flex-col">
 * 
 * HOW THIS REACTS ON SCALING:
 * - On Mobile (No Prefix): 
 *      `border-t`, `fixed`, `bottom-0`, `w-full`
 *      The navigation bar snaps horizontally across the very bottom of the screen (app-like).
 * - On Desktop (When browser width > 768px (`md:` prefix activates)):
 *      `md:border-r`, `md:border-t-0`, `md:relative`, `md:w-64`, `md:h-screen`, `md:flex-col`
 *      The bottom bar completely transforms! It drops the fixed positioning, snaps to the left side 
 *      of the screen, takes up 100% of the vertical height (`h-screen`), restricts its width to 16rem (`w-64`), 
 *      and stacks the icons vertically (`flex-col`).
 * 
 * 
 * =========================================================
 * 2. MVC RELATIONSHIPS (How everything talks to each other)
 * =========================================================
 *  [ DATABASE ] <---> [ MODELS ] <---> [ CONTROLLERS ] <---> [ VIEWS / JAVASCRIPT ]
 * 
 * 1. The user clicks a Like button in the View Javascript.
 * 2. Javascript asynchronously sends a POST request to a Route mapped to the Like Controller.
 * 3. The Controller asks the LikeModel to check the database logic (has this exact user liked this exact post?).
 * 4. The Model performs the SQL query, returns the boolean answer to the Controller.
 * 5. The Controller returns a JSON response to the Javascript.
 * 6. The Javascript sees `res.success` and turns the heart red without the browser ever reloading.
 * 
 * 
 * =========================================================
 * 3. DYNAMIC NETWORK ROUTING (App.php)
 * =========================================================
 * CodeIgniter forcefully uses $baseURL to generate images/redirects. Normal apps hardcode this.
 * Unyun has a custom sniffer that changes its baseURL dynamically so you can share it via Ngrok
 * or Local Wi-Fi without infinite redirect loops breaking mobile testing.
 * 
 * Location: `app/Config/App.php`
 * 
 * public function __construct() {
 *     parent::__construct();
 *     
 *     // If Ngrok is tunneling the connection, intercept the original Ngrok Domain and bind CODEIGNITER to it.
 *     if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
 *         $protocol = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https://' : 'http://';
 *         $this->baseURL = $protocol . $_SERVER['HTTP_X_FORWARDED_HOST'] . '/';
 *     } 
 *     // Otherwise, fallback to the local IP address (e.g. 192.168.1.30 or localhost)
 *     elseif (isset($_SERVER['HTTP_HOST'])) {
 *         $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
 *         $this->baseURL = $protocol . $_SERVER['HTTP_HOST'] . '/';
 *     }
 * }
 * 
 * 
 * =========================================================
 * 4. CONTROLLERS EXPLAINED
 * =========================================================
 * Controllers handle intense processing such as image compression and authorization checking.
 * 
 * --- Post Controller (Handling Media Uploads) ---
 * Location: `app/Controllers/Post.php`
 * Logic: When a user uploads a media file, Unyun figures out if it is an image or video based on extension.
 *        If it is an image, it utilizes PHP's \Config\Services::image() to mathematically compress 
 *        the pixels and reduce file sizes by up to 80% to save bandwidth.
 * 
 * Snippet Example:
 *      if (in_array(strtolower($ext), ['jpg','jpeg','png','webp'])) {
 *          // Load the image manipulation library
 *          $imageService = \Config\Services::image()
 *              ->withFile($file->getTempName())
 *              ->resize(1080, 1080, true, 'auto') // Scale image down to standard social media size
 *              ->save($savePath, 75); // Compress quality to 75%
 *      }
 * 
 * 
 * --- Quickie Controller (Infinite TikTok Clone) ---
 * Location: `app/Controllers/Quickie.php`
 * Logic: Instead of loading 50 videos on initial page load (which would crash phones), it only loads 3.
 *        When the user scrolls `window.innerHeight`, JS fetches `nextPosts()`.
 *        This controller uses an SQL `ORDER BY RAND()` mixed with an offset to spit back pure JSON data.
 * 
 * Snippet Example:
 *     public function nextPosts($offset = 0) {
 *         // Call Model to fetch 3 posts bypassing the ones we already saw
 *         $posts = $postModel->getFeedPosts($offset, 3);
 *         return $this->response->setJSON(['success' => true, 'posts' => $posts]);
 *     }
 * 
 * 
 * =========================================================
 * 5. JAVASCRIPT MECHANICS (The Unified Modal & Observers)
 * =========================================================
 * The View layer contains Vanilla ES6 Javascript. 
 * 
 * --- The Unified Architecture Fix ---
 * Problem: Originally, opening a popup modal and then closing it destroyed the HTML skeleton, breaking comments.
 * Fix: The code strictly manipulates `classList.add('hidden')` natively. 
 * Location: `app/Views/profile/view.php`
 * 
 * Snippet Example:
 *     function showUnifiedModal(postId, withMedia) {
 *         if (withMedia) {
 *             // User clicked the Post Picture! Reveal the massive left-side video container.
 *             postModalMedia.classList.remove('hidden');
 *             postModalMedia.classList.add('md:flex', 'flex-col');
 *         } else {
 *             // User clicked the tiny Comment Bubble! Hide the entire left-side video container.
 *             // DO NOT USE .innerHTML = ''; as it permanently destroys the DOM nodes!
 *             postModalMedia.classList.add('hidden');
 *             postModalMedia.classList.remove('md:flex', 'flex-col');
 *         }
 *         modal.classList.remove('hidden'); // Reveal the background gray shadow
 *     }
 * 
 * --- The Intersection Observer (Saving Browser Memory) ---
 * When rendering a feed (`feed.php`), having 10 HTML5 `<video>` tags playing at once causes extreme lag.
 * Javascript monitors the literal screen pixels to see what the user is looking at.
 * 
 * Snippet Example:
 *     let observer = new IntersectionObserver((entries) => {
 *         entries.forEach(entry => {
 *             let media = entry.target;
 *             if (entry.isIntersecting) {
 *                 // The video has entered the viewport! Execute .play() safely.
 *                 media.play();
 *             } else {
 *                 // The user scrolled past it! Force it to .pause() to free RAM instantly.
 *                 media.pause();
 *             }
 *         });
 *     }, { threshold: 0.6 }); // Requires at least 60% of the video to be visibly on screen
 * 
 * 
 * =========================================================
 * 6. MODELS (Database Retrieval Integrity)
 * =========================================================
 * Models encapsulate the SQL syntax. 
 * 
 * --- CommentModel (SQL Joining) ---
 * `app/Models/CommentModel.php`
 * Instead of asking the DB for a comment, and then doing a SECOND DB call to ask for the user's name,
 * the model cleanly JOINs them.
 * 
 * Snippet Example:
 *      $this->db->table('comments')
 *           ->select('comments.*, users.username, users.profile_pic')
 *           ->join('users', 'users.id = comments.user_id')
 *           ->where('comments.post_id', $postId)
 *           ->orderBy('comments.created_at', 'DESC');
 * 
 * This guarantees the JSON generated natively contains all info the Frontend Javascript needs to paint the 
 * display profile picture elegantly without further processing.
 */
