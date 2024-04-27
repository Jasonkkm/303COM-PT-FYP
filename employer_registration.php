<!DOCTYPE html>
<html>
<head>
<title>Registration Page</title>
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
<h1>Employer Registration Page</h1>

<?php
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $numPeople = $_POST['num_people'];
    $roomType = $_POST['room_type'];
    $haveBaby = $_POST['have_baby'];
    $haveOlder = $_POST['have_older'];
	$languages = $_POST['languages'];
    $needCookHelper = $_POST['need_cook_helper'];
	$needExperience = $_POST['years_of_experience'];

    // Validate the form data
	
		    // Calculate Score 
    $score1 = 0;
    $score2 = 0;
    
// Calculate score based on $numPeople, $roomType, $haveBaby, $haveOlder, and $needCookHelper
if ($numPeople < 3) {
    $score1 += 1;
} elseif ($numPeople > 3 && $numPeople <= 4) {
    $score1 += 3;
} elseif ($numPeople > 4) {
    $score1 += 5;
}

if ($roomType === 'noroom') {
    $score1 += 5;
} elseif ($roomType === 'shared') {
    $score1 += 3;
} elseif ($roomType === 'single') {
    $score1 += 1;
}

if ($haveBaby === 'yes') {
    $score1 += 3;
} elseif ($haveBaby === 'no') {
    $score1 += 0;
}

if ($haveOlder === 'yes') {
    $score1 += 3;
} elseif ($haveOlder === 'no') {
    $score1 += 0;
}

if ($languages === 'fluent') {
    $score1 += 3;
} elseif ($languages === 'common') {
    $score1 += 2;
} elseif ($languages === 'fair') {
    $score1 += 1;
}

if ($needCookHelper === 'yes') {
    $score1 += 3;
} elseif ($needCookHelper === 'no') {
    $score1 += 0;
}

// Calculate score based on total working years
if ($_POST['years_of_experience'] == 0) {
    $score2 = 0;
} elseif ($_POST['years_of_experience'] >= 1 && $_POST['years_of_experience'] <= 3) {
    $score2 = 3;
} elseif ($_POST['years_of_experience'] > 3 && $_POST['years_of_experience'] <= 5) {
    $score2 = 5;
} elseif ($_POST['years_of_experience'] > 5) {
    $score2 = 10;
}

	
	

    // Check if passwords match
    if ($password !== $confirmPassword) {
        $errorMessage = "Passwords do not match."; 
    } else {
        // Perform additional validation and processing as per your requirements

        // Insert the data into the database
        require_once('dbconfig.php');
        $conn = new mysqli($host, $username_db, $password_db, $database);
		
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "INSERT INTO employer_registration (username, password, confirm_password, num_people, room_type, have_baby, have_older, languages, need_cook_helper, need_personalScore, need_expScore ,need_experience) VALUES ('$username', '$password', '$confirmPassword', '$numPeople', '$roomType', '$haveBaby', '$haveOlder', '$languages','$needCookHelper', '$score1', '$score2', '$needExperience')";

// After successful registration
if ($conn->query($sql) === true) {
    // Registration successful, now redirect to the employer homepage
    header('Location: registration_confirmation.php');
    exit;
} else {
    // Handle errors, for example, display an error message
    $errorMessage = "Error: " . $sql . " " . $conn->error;
}

        $conn->close();
    }
}
?>
<div style="width: 100%; display: flex; flex-direction: row; justify-content: center; align-content: center; padding: 20px 20px;">
<form action="employer_registration.php" method="POST" style="min-width: 500px; padding: 20px 20px; background-color: #fff; border-radius: 10px;">
    <label for="username">Name:</label><br>
    <input type="text" name="username" id="username" required><br><br>

    <label for="password">Password:</label><br>
    <input type="password" name="password" id="password" required><br><br>

    <label for="confirm_password">Confirm Password:</label><br>
    <input type="password" name="confirm_password" id="confirm_password" required><br><br>

    <label for="num_people">How Many Family Member :</label><br>
    <input type="number" name="num_people" id="num_people" required><br><br>

    <label for="room_type">Room for Helper:</label><br>
    <select name="room_type" id="room_type" required>
            <option value="single">Single Room</option>
            <option value="shared">Shared Room</option>
			<option value="noroom">No Room</option>
    </select><br><br>
	
	 <label for="have_baby">Have Baby:</label><br>
        <select id="have_baby" name="have_baby">
            <option value="yes">Yes</option>
            <option value="no">No</option>
        </select><br><br>


    <label for="have_older">Do you have an older person?</label><br>
        <select id="have_older" name="have_older">
            <option value="yes">Yes</option>
            <option value="no">No</option>
        </select><br><br>
		
	<label for="languages">Helper's English Speaking:</label><br>
        <select id="languages" name="languages">
            <option value="fluent">Fluent</option>
            <option value="common">Common</option>
			<option value="fair">Fair</option>
        </select>	<br><br>
	

    <label for="need_cook_helper">Do you need a helper to cook?</label>	<br>
	    <select id="need_cook_helper" name="need_cook_helper">
            <option value="yes">Yes</option>
            <option value="no">No</option>
        </select><br><br>

    <label for="years_of_experience">Request Working Experience (Year):</label><br>
    <input type="number" name="years_of_experience" id="years_of_experience"><br><br>

    <input type="submit" value="Register">
</form>
</div>

</body>
</html>