<?php

// Include database connection settings
require_once('dbconfig.php');

// Create connection
$conn = new mysqli($host, $username_db, $password_db, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $account_type = $_POST["account_type"];

    // Validate user credentials against the database
    if ($account_type === "agency") {
        $table = "agency_registration";
    } elseif ($account_type === "employer") {
        $table = "employer_registration";
    }

    $sql = "SELECT * FROM $table WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // User credentials are correct
        session_start();
        $_SESSION['account_type'] = $account_type;
        $_SESSION['username'] = $username;

        if ($account_type === "agency") {
            // Redirect to agency_homepage.php
            header("Location: agency_homepage.php");
            exit();
        } elseif ($account_type === "employer") {
            // Redirect to employer_homepage.php
            header("Location: employer_homepage.php");
            exit();
        }
    } else {
        // User credentials are incorrect
        $errorMessage = "Invalid username or password.";
    }
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Date</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css" media="screen">
@import url("css/layout.css");

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
<div style="display: flex; flex-direction: column;">
  <div style="width: 100%; display: flex; flex-direction: row; justify-content: center; align-content: center; padding: 20px 20px;">
    <div class="logo"><img style="width:120px;" src="images/logo.png" alt="" /></div>
  </div>
  <div style="wdith: 100%; display: flex; justify-content: center; align-content: center;">
    <div style="width: 600px;background-color: white; padding: 10px 10px; border-radius: 10px; display: flex; flex-direction: column; justify-content: center; align-content: center; padding: 20px 20px;">
     <div>
        <h2>Member Login</h2>

      <?php if (isset($errorMessage)) { ?>
        <p><?php echo $errorMessage; ?></p>
    <?php } ?>
    <form action="index.php" method="POST">
		<div style="wdith: 100%; display: flex; justify-content: center; align-content: center; padding: 20px 20px; ">
		<div>
        <label for="username">Username:</label><br>
        <input type="text" name="username" id="username" required><br><br>
        <label for="password">Password:</label><br>
        <input type="password" name="password" id="password" required><br><br>
        <label for="account_type">Account Type:</label><br>
        <select name="account_type" id="account_type" required>
            <option value="employer">employer</option>
            <option value="agency">Agency</option>
        </select><br><br>
        <input type="submit" value="Login">
		</div>
		</div>
    </form>
    </div>
	<div style="display: flex; flex-direction: row; margin-top: 20px;justify-content: center; align-content: center;">
	<div style="display: flex; flex-direction: row;">
      <div style="margin: 0px 10px;font-size:14px; padding: 10px 10px; border-radius: 10px;background-color:#0061FE;"><a style="color: white;" href="employer_registration.php">Employer Registration</a></div>
      <div style="margin: 0px 10px;font-size:14px; padding: 10px 10px; border-radius: 10px;background-color:#FE5E53;"><a style="color: white;" href="agency_registration.php">Agency Registration</a></div>
      </div>
    </div>
    </div>
	</div>

<div id="">
      <div id="">
        <ul style="text-align:right; color:#FFF; padding:10px 0 0 0;">
        </ul>
      </div>
    </div>
  </div>
</div>
</body>
</html>
