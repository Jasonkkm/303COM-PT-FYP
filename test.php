<?php
// Start the session
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection settings
require_once('dbconfig.php');

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the username and password from the form
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Create a new database connection
    $conn = new mysqli($host, $username_db, $password_db, $database);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Prepare the SQL statement to select the user data
    $sql = "SELECT id, password FROM agency_registration WHERE username = ?";
    
    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the input parameter
        $stmt->bind_param("s", $username);
        // Execute the query
        $stmt->execute();
        // Bind the result parameter
        $stmt->bind_result($userId, $hash);
        // Fetch the result
        if ($stmt->fetch()) {
            // Verify the password
            if (password_verify($password, $hash)) {
                // Password is correct, so start a new session
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                // Redirect to the user's home page
                header('Location: agency_homepage.php');
                exit;
            } else {
                // Password is not correct
                $errorMessage = "Invalid username or password.";
            }
        } else {
            // No user exists with the entered username
            $errorMessage = "Invalid username or password.";
        }
        // Close the statement
        $stmt->close();
    }
    // Close the database connection
    $conn->close();
}

// If there's an error message, display it
if (isset($errorMessage)) {
    echo "<p>Error: $errorMessage</p>";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <form action="test.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <input type="submit" value="Login">
    </form>
</body>
</html>