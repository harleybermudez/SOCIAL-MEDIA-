<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Class UserModel
 * 
 * Interacts directly with the `users` database table.
 * Strictly defines which fields can be modified securely using CodeIgniter's $model->save() functions,
 * defending against Mass Assignment Vulnerabilities.
 */
class UserModel extends Model
{
    // The exact SQL table this model connects to
    protected $table = 'users';
    
    // The primary key used for automated searches like `$model->find($id)`
    protected $primaryKey = 'id';

    /**
     * White-listed Security Buffer
     * Only fields listed here can be inserted or changed using CodeIgniter entity saves.
     */
    protected $allowedFields = [
        'username',      // Display name
        'email',         // Login credential
        'password',      // Bcrypt Hashed password string
        'profile_pic',   // String path to local filesystem avatar
        'bio'            // Text string for profile description
    ];
}