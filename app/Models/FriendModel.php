<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Class FriendModel
 *
 * CodeIgniter Table Mapping for the `friends` database table.
 * Represents both pending friend requests and accepted friendships.
 */
class FriendModel extends Model
{
    // Relationship table that stores sender/receiver pairs.
    protected $table = 'friends';

    // Unique friendship/request row identifier.
    protected $primaryKey = 'id';

    /**
     * White-listed Database Columns.
     * `status` should generally be `pending` or `accepted`.
     */
    protected $allowedFields = [
        'sender_id',   // User who initiated the request
        'receiver_id', // User who receives the request
        'status'       // Relationship state: pending/accepted
    ];
}
