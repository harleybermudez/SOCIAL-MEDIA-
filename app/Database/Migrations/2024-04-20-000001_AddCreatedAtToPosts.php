<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: AddCreatedAtToPosts
 * 
 * This migration is responsible for adding the 'created_at' timestamp column
 * to the 'posts' table. This allows the application to track exactly when 
 * each post was created, which is essential for sorting the feed chronologically.
 */
class AddCreatedAtToPosts extends Migration
{
    /**
     * The up() method runs when the migration is executed (e.g., via `php spark migrate`).
     * It safely modifies the database schema to add the new column.
     *
     * @return void
     */
    public function up()
    {
        // Safety Check 1: Ensure the 'posts' table actually exists before trying to modify it.
        // If it doesn't exist, we exit early to prevent a fatal database error.
        if (!$this->forge->tableExists('posts')) {
            return;
        }
        
        // Fetch all existing columns in the 'posts' table to check for duplicates.
        $fields = $this->db->getFieldData('posts');
        $columnExists = false;
        
        // Iterate through the columns to see if 'created_at' was already added
        // (perhaps manually or by a previous failed run).
        foreach ($fields as $field) {
            if ($field->name === 'created_at') {
                $columnExists = true;
                break;
            }
        }
        
        // Safety Check 2: Only add the column if it doesn't currently exist.
        if (!$columnExists) {
            // Use the Database Forge class to alter the table structure.
            $this->forge->addColumn('posts', [
                'created_at' => [
                    'type'       => 'TIMESTAMP',
                    'default'    => 'CURRENT_TIMESTAMP', // Automatically set to current time on row insert
                    'null'       => false,               // Do not allow null values
                ],
            ]);
        }
    }

    /**
     * The down() method runs when the migration is rolled back (e.g., via `php spark migrate:rollback`).
     * It reverses the actions of the up() method, returning the database to its previous state.
     *
     * @return void
     */
    public function down()
    {
        // Safety Check 1: Ensure the table exists before attempting to drop a column from it.
        if ($this->forge->tableExists('posts')) {
            
            // Fetch all columns to verify the column we want to remove actually exists.
            $fields = $this->db->getFieldData('posts');
            $columnExists = false;
            
            // Iterate to find the 'created_at' column
            foreach ($fields as $field) {
                if ($field->name === 'created_at') {
                    $columnExists = true;
                    break;
                }
            }
            
            // Safety Check 2: Only attempt to drop the column if it is confirmed to exist.
            if ($columnExists) {
                // Drop the column, completely reverting this specific migration's changes.
                $this->forge->dropColumn('posts', 'created_at');
            }
        }
    }
}
