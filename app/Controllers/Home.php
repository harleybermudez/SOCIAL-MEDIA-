<?php

namespace App\Controllers;

/**
 * Class Home
 * 
 * Default CodeIgniter boilerplate controller.
 * Can be used as a fallback route for the root domain depending on Config/Routes.php.
 */
class Home extends BaseController
{
    /**
     * Renders the default welcome message.
     */
    public function index(): string
    {
        return view('welcome_message');
    }
}
