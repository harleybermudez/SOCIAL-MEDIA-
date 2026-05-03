<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Class PostModel
 * 
 * CodeIgniter Table Mapping for the `posts` Database Table.
 * Defines the strict columns that are legally allowed to be inserted or updated during Post creation.
 */
class PostModel extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'id';

    /**
     * White-listed Database Columns.
     * Any associative array keys passed to $model->save() that are NOT in this list will be securely stripped.
     */
    protected $allowedFields = [
        'user_id',    // The author's unique ID
        'image',      // The 1080p compressed file string mapping
        'caption',    // The post's text description 
        'is_quickie', // Boolean flag determining if it routes to the Reels Feed (1) or Image Feed (0)
        'music'       // Name of the audio mp3 file string mappings
    ];
}