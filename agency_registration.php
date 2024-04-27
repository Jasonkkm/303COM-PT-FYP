<!DOCTYPE html>
<html>
	<head>
		<title>Agency Registration</title>
		<style type="text/css" media="screen">

body{background:#baeaa0 url(../images/body_bg.gif) repeat-x 0 0}

input[type=file] {
  border: none !important;
  border-radius: 20px !important;
  box-shadow: none !important;
}

input[type=file]::file-selector-button {
  margin-right: 20px;
  border: none;
  background: #084cdf;
  padding: 5px 10px;
  border-radius: 10px;
  color: #fff;
  cursor: pointer;
  transition: background .2s ease-in-out;
  border: none !important;
  border-radius: 20px !important;
  box-shadow: none !important;
}

input[type=file]::file-selector-button:hover {
  background: #0d45a5;
}

a:link, a:visited {
	padding: 5px 10px;
	border-radius: 20px;
	background-color: #0061FE;
	color: #fff;
    text-decoration: none;
    color: #fff;
    border-bottom: solid 2px #0b367a;
} 
                                           
a:hover, a:focus, a:active {
    text-decoration: none; 
    color: #fff;
	background-color:#0b367a;
    border-bottom: solid 2px #0b367a;
} 
	
table {
	border-collapse: collapse;
    font-family: Tahoma, Geneva, sans-serif;
}
table td {
	padding: 15px;
}
table tr th {
	background-color: #f8f983;
	color: #7f7f7f;
	font-weight: 500;
	font-size: 13px;
	border: 1px solid #f8f983;
	padding: 10px 5px;
}
table tbody td {
	color: #636363;
	border: 0px solid #dddfe1;
	text-align: center;
	font-size: 12px;
}
table tbody tr {
	background-color: #e6ffe8;
}
table tbody tr:nth-child(odd) {
	background-color: #ffffff;
}

input[type=button], input[type=submit], input[type=reset] {
background: #0c89eb;
color: #fff;
border: 1px solid #eee;
border-radius: 20px;
box-shadow: 5px 5px 5px #eee;
text-shadow: none;
cursor: pointer;
}

input[type=button]:hover, input[type=submit]:hover, input[type=reset]:hover {
background: #016ABC;
color: #fff;
border: 1px solid #eee;
border-radius: 20px;
box-shadow: 5px 5px 5px #eee;
text-shadow: none;
}

input{

                    position: relative;
                    cursor: text;
                    font-size: 14px;
                    line-height: 20px;
                    padding: 0 16px;
                    height: 28px;
                    background-color: #fff;
                    border: 1px solid #d6d6e7;
                    border-radius: 3px;
                    color: rgb(35, 38, 59);
                    box-shadow: inset 0 1px 4px 0 rgb(119 122 175 / 30%);
                    overflow: hidden;
                    transition: all 100ms ease-in-out;
                    :focus {
                        border-color: #3c4fe0;
                        box-shadow: 0 1px 0 0 rgb(35 38 59 / 5%);
                    }



		</style>
	</head>
	<body>
	<h1>Agency Registration</h1>
<?php
session_start();  // Start a new session or resume the existing one

// Include database configuration
require_once('dbconfig.php');

// Create a connection to the database
$conn = new mysqli($host, $username_db, $password_db, $database);

// Check if the database connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize the user input to prevent SQL Injection
    $username = $conn->real_escape_string($_POST['username']);
    $agencyUsername = $conn->real_escape_string($_POST['agency_username']);
    $agencyTel = $conn->real_escape_string($_POST['agency_tel']);
    $password = $_POST['password']; // Hash the password securely

    // Prepare an SQL query to check if the username already exists
    $checkSql = "SELECT * FROM agency_registration WHERE username = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    // Check if a record exists with the same username
    if ($checkResult->num_rows > 0) {
        $errorMessage = "An account with this username already exists.";
    } else {
        // Prepare an SQL query to insert the new agency's data into the database
        $insertSql = "INSERT INTO agency_registration (username, agency_username, agency_tel, password) VALUES (?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ssss", $username, $agencyUsername, $agencyTel, $password);
        $insertStmt->execute();

        // Check if the insertion was successful
        if ($insertStmt->affected_rows > 0) {
            // Set session variables and redirect to the agency's homepage on successful registration
            $_SESSION['username'] = $username;
            $_SESSION['agency_id'] = $conn->insert_id; // Get the auto-incremented ID
            $_SESSION['account_type'] = 'agency';
            header('Location: agency_homepage.php');
            exit;
        } else {
            $errorMessage = "Registration failed. Please try again.";
        }

        // Close the insert statement
        $insertStmt->close();
    }

    // Close the statement used for checking the username
    $checkStmt->close();
}

// Close the database connection
$conn->close();
?>




	<?php if (isset($errorMessage)) { ?>
	<p><?php echo $errorMessage; ?></p>
	<?php } ?>
	<div style="width: 100%; display: flex; flex-direction: row; justify-content: center; align-content: center; padding: 20px 20px;">
		<form action="agency_registration.php" method="POST" style="min-width: 500px; padding: 20px 20px; background-color: #fff; border-radius: 10px;">
			<label for="username">Username:</label><br>
			<input type="text" name="username" id="username" required><br><br>
			<label for="agency_username">Agency Full Name:</label><br>
			<input type="text" name="agency_username" id="agency_username" required><br><br>
			<label for="agency_tel">Tel:</label><br>
			<input type="text" name="agency_tel" id="agency_tel" required><br><br>
			<label for="password">Password:</label><br>
			<input type="password" name="password" id="password" required><br><br>
			<label for="confirm_password">Confirm Password:</label><br>
			<input type="password" name="confirm_password" id="confirm_password" required><br><br>
			<input type="submit" value="Register">
		</form>
	</div>
	</body>
</html>