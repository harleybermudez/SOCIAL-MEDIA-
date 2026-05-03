<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Class CommentModel
 *
 * CodeIgniter Table Mapping for the `comments` database table.
 * Used when comment rows need standard Model behavior instead of direct Query Builder calls.
 */
class CommentModel extends Model
{
    // Physical database table containing post comments.
    protected $table = 'comments';

    // Unique row identifier used for updates, deletes, and find lookups.
    protected $primaryKey = 'id';

    /**
     * White-listed Database Columns.
     * Only these fields may be inserted or updated through this model.
     */
    protected $allowedFields = [
        'user_id',    // The account that wrote the comment
        'post_id',    // The post receiving the comment
        'comment'     // The raw comment body submitted by the user
    ];
}
