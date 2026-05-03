<?php

namespace App\Controllers;

/**
 * Class Test
 *
 * Temporary development controller for one-off database checks or local schema fixes.
 * This should not be exposed in production without authentication or removal.
 */
class Test extends BaseController
{
    /**
     * Adds the `is_quickie` flag to posts during local development.
     * Route: GET /test
     *
     * @return void
     */
    public function index()
    {
        $db = \Config\Database::connect();

        // Execute a raw schema patch and report whether it succeeded or was already applied.
        try {
            $db->query("ALTER TABLE posts ADD COLUMN is_quickie TINYINT(1) DEFAULT 0;");
            echo "Successfully added is_quickie column!";
        } catch (\Exception $e) {
            echo "Error or already added: " . $e->getMessage();
        }
    }
}
