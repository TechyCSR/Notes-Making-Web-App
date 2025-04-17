<?php
require_once 'config.php';

try {
    // Create connection without database name
    $pdo = new PDO(
        "mysql:host=" . DB_HOST,
        DB_USER,
        DB_PASS,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    // Read and execute SQL schema
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($sql);

    echo "Database initialized successfully!\n";
} catch (PDOException $e) {
    die("Database initialization failed: " . $e->getMessage() . "\n");
} 