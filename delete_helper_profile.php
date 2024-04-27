<?php
// Connect to the database
require_once('dbconfig.php');
$conn = new mysqli($host, $username_db, $password_db, $database);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the Helper ID from URL
$helperId = $_GET['id'];

// SQL to delete a record
$sql = "DELETE FROM helper_profiles WHERE id = ?";

// Prepare statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $helperId);
if ($stmt->execute()) {
    echo "Record deleted successfully";
    header('Location: agency_homepage.php'); // Redirect back to the homepage
    exit;
} else {
    echo "Error deleting record: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>