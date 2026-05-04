<?php
/**
 * Custom Migration Script: Add 'created_at' to 'comments' table
 * 
 * This standalone script manually executes a raw SQL query to alter the database schema
 * outside of the standard CodeIgniter migration framework.
 */

// Autoload dependencies to ensure we can access CodeIgniter's core classes
require 'vendor/autoload.php';

// Establish a connection to the default database using CodeIgniter's configuration
$db = \Config\Database::connect();

try {
    // Attempt to execute the raw SQL query to add the new timestamp column
    $db->query("ALTER TABLE comments ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");
    
    // Output a success message if the query executes without throwing an exception
    echo "✓ Column created_at successfully added to comments table\n";
} catch (\Exception $e) {
    // Catch any exceptions thrown by the database during the query execution
    
    // Check if the error is specifically because the column has already been added
    // This allows the script to be safely run multiple times without crashing
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "✓ Column created_at already exists on comments table\n";
    } else {
        // If it's a different error (e.g., syntax error, connection issue), output the error message
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}
?>
