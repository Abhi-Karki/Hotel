<?php
// Database configuration
$host = "localhost";      // usually 'localhost'
$db   = "hotel";          // your database name
$user = "root";           // your database username
$pass = " ";               // your database password
$charset = "utf8mb4";     // character set

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options for better error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // use real prepared statements
];

// Create PDO instance
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Stop script and show error message if connection fails
    die("Database connection failed: " . $e->getMessage());
}
?>
