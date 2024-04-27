<?php
session_start();

// Set the inactivity timeout period in seconds (3 minutes in this example)
$timeoutSeconds = 180;

// Check if the last activity timestamp is set in the session
if (isset($_SESSION['last_activity'])) {
    // Calculate the time difference between the current time and the last activity time
    $inactiveSeconds = time() - $_SESSION['last_activity'];

    // Check if the inactive time exceeds the timeout period
    if ($inactiveSeconds >= $timeoutSeconds) {
        // Destroy the session and redirect to the login page
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
}

// Update the last activity timestamp in the session
$_SESSION['last_activity'] = time();

// Clear all session variables except the last activity timestamp
$lastActivity = $_SESSION['last_activity'];
session_unset();
$_SESSION['last_activity'] = $lastActivity;

// Redirect to the login page
header('Location: index.php');
exit;
?>