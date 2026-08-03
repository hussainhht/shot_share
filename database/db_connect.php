<?php

$host = 'localhost';
$database = 'shot_share';
$user = 'root';
$db_password = '';

$dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $conn = new PDO(
        $dsn,
        $user,
        $db_password,
        $options
    );

} catch (PDOException $e) {
    die(
        'Database connection failed: ' .
        $e->getMessage()
    );
}