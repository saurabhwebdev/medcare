<?php
require_once 'database.php';

try {
    // Create database if it doesn't exist
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    $pdo->exec($sql);
    echo "Database created successfully<br>";

    // Switch to the created database
    $pdo->exec("USE " . DB_NAME);

    // Read and execute schema.sql
    $schema = file_get_contents('schema.sql');
    $pdo->exec($schema);
    echo "Tables created successfully<br>";

    echo "Database initialization completed successfully!";
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?> 