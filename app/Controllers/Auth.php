<?php

namespace App\Controllers;

use App\Models\UserModel;

/**
 * Class Auth
 * 
 * Handles all authentication-related logic including Login, Registration, 
 * Logout, Password Resets, and Session configurations.
 * Allows access control throughout the entire application.
 */
class Auth extends BaseController
{
    /**
     * Display the Login View.
     * Route: GET /login
     *
     * @return string View mapping to `app/Views/auth/login.php`
     */
    public function login()
    {
        return view('auth/login');
    }

    /**
     * Display the Registration View.
     * Route: GET /register
     *
     * @return string View mapping to `app/Views/auth/register.php`
     */
    public function register()
    {
        return view('auth/register');
    }

    /**
     * Process new user registrations.
     * Route: POST /register
     * 
     * Expects: `username`, `email`, `password`, `bio`, and `profile_pic` (File).
     * Debugging Note: If avatars are failing to upload, verify that `public/uploads/profile_pics` 
     * exists and possesses 0777 write permissions.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function store()
    {
        // 1. Strict Validation Layer
        $rules = [
            'username' => 'required|min_length[3]|max_length[30]|alpha_numeric|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]'
        ];

        if (!$this->validate($rules)) {
            // Combine all validation errors into a single string for the flash message
            $errorString = implode('<br>', $this->validator->getErrors());
            return redirect()->back()->withInput()->with('error', $errorString);
        }

        $model = new UserModel();

        // Retrieve file object from the form input explicitly named 'profile_pic'
        $file = $this->request->getFile('profile_pic');
        $profilePicName = null;

        // Verify if a real file was uploaded and hasn't error'd out in PHP's tmp folder
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Generate a random string name to prevent file clashing (e.g. two users uploading "me.png")
            $profilePicName = $file->getRandomName();
            $file->move('uploads/profile_pics', $profilePicName);
            
            /** 
             * NATIVE IMAGE COMPRESSION BLOCK
             * We massively scale down avatars to 250x250 pixels dynamically to save layout rendering bounds.
             * 
             * Debugging Note: If \Config\Services::image() throws a Fatal Error, ensure the PHP GD
             * extension is enabled inside Laragon -> Tools -> Quick Settings -> PHP Extensions.
             */
            try {
                \Config\Services::image()
                    ->withFile('uploads/profile_pics/' . $profilePicName)
                    ->resize(250, 250, true, 'auto')
                    ->save('uploads/profile_pics/' . $profilePicName, 60); // 60% Quality reduction
            } catch (\Exception $e) {
                // If compression fails (e.g., file isn't an image), fail silently and keep original
            }
        }

        // Execute MySQL Insertion via CodeIgniter Query Builder
        $model->save([
            'username'     => $this->request->getPost('username'),
            'email'        => $this->request->getPost('email'),
            // DO NOT SAVE PLAINTEXT PASSWORDS! Use BCRYPT algorithm natively.
            'password'     => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'bio'          => $this->request->getPost('bio'),
            'profile_pic'  => $profilePicName
        ]);

        return redirect()->to('/login');
    }

    /**
     * Authenticate an existing user based on email.
     * Route: POST /login
     * 
     * Expects: `email`, `password`.
     * Debugging Note: This function utilizes PHP native `password_verify`. If log ins are failing, 
     * inspect the DB: ensure `password` column is exactly `VARCHAR(255)`, otherwise BCRYPT trims hash.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function auth()
    {
        $model = new UserModel();

        // Ask the database if any user possesses this EXACT email address
        $user = $model->where('email', $this->request->getPost('email'))->first();

        if (!$user) {
            // Redirect back with an attached Flashdata error message overlay
            return redirect()->back()->with('error', 'User not found! Did you create an account first?');
        }

        /**
         * Compare the typed plain-text password to the DB hashed password.
         * Note: If password_verify continuously fails, it is usually because the password hash inside the DB 
         * was modified manually or truncated heavily.
         */
        if (!password_verify($this->request->getPost('password'), $user['password'])) {
            return redirect()->back()->with('error', 'Wrong password! (DB hash length: ' . strlen($user['password']) . ')');
        }

        // If validation strictly passes...
        if ($user && password_verify($this->request->getPost('password'), $user['password'])) {

            // Initiate CodeIgniter Session Array. (Required to lock protected routes)
            session()->set([
                'user_id'   => $user['id'],
                'username'  => $user['username'],
                'logged_in' => true
            ]);

            // Jump to main timeline
            return redirect()->to('/feed');
        }

        return redirect()->back()->with('error', 'Invalid login');
    }

    /**
     * Discard active session and force user into logged-out state.
     * Route: GET /logout
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logout()
    {
        // Wipes all global session trackers
        session()->destroy();
        return redirect()->to('/login');
    }

    /**
     * Display the Reset Password View.
     * Route: GET /reset-password
     * 
     * Debugging Note: This requires an explicit '?email=xyz' query parameter in the URL.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function resetPassword()
    {
        $email = $this->request->getGet('email');
        if (!$email) {
            return redirect()->to('/login')->with('error', 'No email provided.');
        }

        return view('auth/reset_password', ['email' => $email]);
    }

    /**
     * Process Reset Password execution.
     * Route: POST /reset-password
     * 
     * Validates new passwords, matches them, securely builds a new hash, and overrides 
     * the forgotten user password in the Database natively.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function resetPasswordSubmit()
    {
        $model = new UserModel();
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Validation Layer
        $rules = [
            'password'         => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            $errorString = implode('<br>', $this->validator->getErrors());
            return redirect()->back()->with('error', $errorString);
        }

        // Identification Layer
        $user = $model->where('email', $email)->first();
        if (!$user) {
            return redirect()->to('/login')->with('error', 'User not found.');
        }

        // Execution Layer - Override specific DB Row based on Unqiue ID
        $model->update($user['id'], [
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        return redirect()->to('/login')->with('success', 'Password reset successful! You may now log in.');
    }
}