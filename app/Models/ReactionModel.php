<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Class ReactionModel
 *
 * CodeIgniter Table Mapping for the `reactions` database table.
 * Kept for reaction-style interactions that are separate from the `likes` table.
 */
class ReactionModel extends Model
{
    // Physical database table used for richer reaction records.
    protected $table = 'reactions';

    // Unique reaction row identifier.
    protected $primaryKey = 'id';

    /**
     * White-listed Database Columns.
     * Allows a user to attach a specific reaction value to a post.
     */
    protected $allowedFields = [
        'user_id',       // User creating the reaction
        'post_id',       // Post being reacted to
        'reaction_type'  // Reaction value such as like/love/laugh if enabled
    ];
}
