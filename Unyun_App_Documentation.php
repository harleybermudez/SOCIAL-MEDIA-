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
 * 
 * 
 * =========================================================
 * 7. AUTHENTICATION & VALIDATION SECURITY
 * =========================================================
 * Unyun handles user authentication centrally in `app/Controllers/Auth.php`.
 * 
 * --- Sign Up (Registration) ---
 * - Username Constraints: Only permit alphanumeric characters (a-z, A-Z, 0-9). Spaces and special characters are explicitly rejected to prevent routing errors. Must be between 3 and 30 characters.
 * - Email Constraints: Must pass strictly formatted RFC validation (e.g., `user@domain.com`) and must be globally unique in the database (duplicate emails are rejected).
 * - Password Constraints: A strict minimum of 8 characters is required for registration. All characters and symbols are allowed.
 * - Validation Setup: Implemented both frontend (HTML5 `<input type="email">`, `minlength`, `pattern`) and backend (CodeIgniter `$this->validate()` using rules like `valid_email`, `is_unique[users.email]`, and `alpha_numeric`).
 * - Security: Passwords are automatically hashed via native PHP `password_hash()` using the default Bcrypt algorithm before storing. Plain text passwords are NEVER stored.
 * 
 * Snippet Example (Backend Validation):
 *      $rules = [
 *          'username' => 'required|min_length[3]|alpha_numeric|is_unique[users.username]',
 *          'email'    => 'required|valid_email|is_unique[users.email]',
 *          'password' => 'required|min_length[8]'
 *      ];
 *      if (!$this->validate($rules)) {
 *          return redirect()->back()->with('error', implode('<br>', $this->validator->getErrors()));
 *      }
 * 
 * --- Login ---
 * - Logic: Requires an email and password match. It fetches the email row and compares the typed password directly against the stored hash using `password_verify()`.
 * - Legacy Support: Login intentionally DOES NOT enforce the 8-character `min_length` rule during the auth check. This guarantees that older accounts (created before the 8-character limit was implemented) are still able to securely log in.
 * 
 * Snippet Example (Secure Authentication Check):
 *      $user = $model->where('email', $this->request->getPost('email'))->first();
 *      
 *      // Compare the plaintext typed password to the 60-character Bcrypt hash in the DB
 *      if ($user && password_verify($this->request->getPost('password'), $user['password'])) {
 *          // Success! Setup the session.
 *          session()->set(['user_id' => $user['id'], 'logged_in' => true]);
 *      }
 * 
 * --- Password Reset ---
 * - Logic: Verifies both `password` and `confirm_password` inputs match exactly.
 * - Constraints: Enforces the exact same 8-character `min_length` rule as registration. If a legacy user (with a 5-character password) decides to reset their password, they will be forced to upgrade to an 8-character password.
 * 
 * Snippet Example (Bcrypt Overwrite):
 *      // Overwrite the specific user's old password with a newly generated Bcrypt hash
 *      $model->update($user['id'], [
 *          'password' => password_hash($password, PASSWORD_DEFAULT)
 *      ]);
 */
