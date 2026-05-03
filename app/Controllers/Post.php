<?php

namespace App\Controllers;

use App\Models\PostModel;

/**
 * Class Post
 * 
 * Central hub for fetching the global feed array and handling new user uploads.
 * Actively processes heavy media logic including Image Compression vs Video Passthrough.
 */
class Post extends BaseController
{
    /**
     * Synthesizes the main timeline feed algorithm.
     * Route: GET /feed
     * 
     * Debugging Note: The subqueries natively count identical rows to generate the Like metrics seamlessly.
     * This avoids N+1 database querying issues (where 50 posts require 50 individual calls to the Like table).
     *
     * @return string Returns the loaded `feed.php` View with an array of all posts attached.
     */
    public function index()
    {
        $model = new PostModel();

        $userId = session()->get('user_id') ?? 0;

        // Assembly of relational data
        $data['posts'] = $model
            ->select("posts.*, users.username, users.profile_pic, 
                      (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) as like_count, 
                      (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = $userId) as has_liked")
            ->join('users', 'users.id = posts.user_id')
            ->orderBy('posts.id', 'DESC')
            ->findAll();

        return view('feed', $data);
    }

    /**
     * Route: GET /post/create
     * Renders the View containing the dropzone HTML form allowing new media uploads.
     */
    public function create()
    {
        return view('post/create');
    }

    /**
     * Executes the heavy lifting for storing new social media posts.
     * Route: POST /post/store
     * 
     * Handles 30MB chunk limits, filters mp4/webm/mov for videos and manipulates jpg/png.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse Fast-routes back to the main timeline feed on completion.
     */
    public function store()
    {
        $model = new PostModel();

        /**
         * 1. Hardcore Validation Constraints
         * Automatically blocks payloads larger than 30MB or anything that isn't a known array of media formats.
         * Server configs like `php.ini` dictate ultimate execution limits (`upload_max_filesize`).
         */
        $validationRule = [
            'image' => [
                'rules' => 'uploaded[image]|max_size[image,30720]|ext_in[image,png,jpg,jpeg,gif,mp4,webm,mov]',
                'errors' => [
                    'max_size' => 'File too large! Maximum allowed is 30MB.',
                    'ext_in'   => 'Invalid file type. Upload a valid image or video.'
                ]
            ]
        ];

        // Bail out immediately if a hacker attempts to push a PHP script or a 1GB file
        if (!$this->validate($validationRule)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors()['image']);
        }

        // 2. File Retrieval
        $file = $this->request->getFile('image');
        $musicFile = $this->request->getFile('music'); // Optional background song for photos
        
        $imageName = null;
        $musicName = null;

        /**
         * 3. Media Processing Engine
         */
        if ($file && $file->isValid() && !$file->hasMoved()) {
            
            // Re-name the file to a completely stripped string so names like "my!@video.mp4" don't crash Linux Apache instances
            $imageName = $file->getRandomName();
            $file->move('uploads/posts', $imageName); // Standard move syntax

            /**
             * 4. Smart Compression Bifurcation
             * If it is a VIDEO format, PHP skips this entirely, uploading natively.
             * If it is an IMAGE format, PHP forcefully scales it to 1080x1080 bounds and
             * obliterates 40% of its native file quality. (A 5MB 4K photo becomes a beautiful 300KB social photo).
             */
            $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif'])) {
                try {
                    \Config\Services::image()
                        ->withFile('uploads/posts/' . $imageName)
                        ->resize(1080, 1080, true, 'auto') // Max optimal Instagram dimensions
                        ->save('uploads/posts/' . $imageName, 60); // Aggressively compress size by 40%
                } catch (\Exception $e) {
                    // Fail silently if compression hits a strict memory boundary or is corrupted metadata
                }
                
                // 5. Music Track Logic
                // Only process music if the primary upload was actually an image (Quickie videos handle their own sound naturally)
                if ($musicFile && $musicFile->isValid() && !$musicFile->hasMoved()) {
                    
                    // Very basic server-side validation against injection
                    $allowedAudio = ['mp3', 'wav', 'mpeg'];
                    $musicExt = strtolower($musicFile->getExtension());
                    
                    if (in_array($musicExt, $allowedAudio)) {
                        $musicName = $musicFile->getRandomName();
                        $musicFile->move('uploads/music', $musicName);
                    }
                }
            }
        }

        // 6. Model Insertion
        $model->save([
            'user_id'    => session()->get('user_id'),
            'image'      => $imageName,
            'music'      => $musicName,
            'caption'    => $this->request->getPost('caption'),
            'is_quickie' => $this->request->getPost('is_quickie') ? 1 : 0 // Flag determining if it shows up in Reels
        ]);

        return redirect()->to('/feed');
    }
}