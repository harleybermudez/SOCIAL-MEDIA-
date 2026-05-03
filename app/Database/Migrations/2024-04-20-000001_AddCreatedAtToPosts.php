<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCreatedAtToPosts extends Migration
{
    public function up()
    {
        // Check if column already exists
        if (!$this->forge->tableExists('posts')) {
            return;
        }
        
        $fields = $this->db->getFieldData('posts');
        $columnExists = false;
        
        foreach ($fields as $field) {
            if ($field->name === 'created_at') {
                $columnExists = true;
                break;
            }
        }
        
        // Only add if it doesn't exist
        if (!$columnExists) {
            $this->forge->addColumn('posts', [
                'created_at' => [
                    'type'       => 'TIMESTAMP',
                    'default'    => 'CURRENT_TIMESTAMP',
                    'null'       => false,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->forge->tableExists('posts')) {
            $fields = $this->db->getFieldData('posts');
            $columnExists = false;
            
            foreach ($fields as $field) {
                if ($field->name === 'created_at') {
                    $columnExists = true;
                    break;
                }
            }
            
            if ($columnExists) {
                $this->forge->dropColumn('posts', 'created_at');
            }
        }
    }
}
