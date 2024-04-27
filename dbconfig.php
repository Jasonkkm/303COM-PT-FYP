<?php
// Database configuration settings
$host = 'localhost';          // Your database host, usually 'localhost'
$database = 'fyp';  // Your database name
$username_db = 'root'; // Your database username
$password_db = ''; // Your database password

// Attempt to connect to the database using the MySQLi extension
$conn = new mysqli($host, $username_db, $password_db, $database);

// Check for a connection error and display a message if applicable
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// If the file is included elsewhere, we don't want to close the connection
// so we comment out the close command. The connection will be closed
// automatically when the script ends.
// $conn->close();
?>