<?php

namespace App\Controllers;

/**
 * Class Reaction
 *
 * Reserved controller for richer reaction behavior.
 * The current app uses LikeController for heart/like toggles, so this class is intentionally passive.
 */
class Reaction extends BaseController
{
    /**
     * Placeholder route handler.
     * Route: not currently registered.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function index()
    {
        return redirect()->to('/feed');
    }
}
