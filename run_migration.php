<?php
require 'vendor/autoload.php';

$db = \Config\Database::connect();

try {
    $db->query("ALTER TABLE comments ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");
    echo "✓ Column created_at successfully added to comments table\n";
} catch (\Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "✓ Column created_at already exists on comments table\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}
?>
