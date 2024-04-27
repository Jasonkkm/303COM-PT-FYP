<?php
session_start();

// Redirect to the login page if the user is not logged in or if the session does not indicate an employer user
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] !== 'employer' || !isset($_SESSION['username'])) {
    header('Location: index.php'); // Replace 'login.php' with your actual login page
    exit;
}

require_once('dbconfig.php');

// Initialize variables for messages and data
$successMessage = '';
$errorMessage = '';
$employerData = array();

// Create a new database connection
$conn = new mysqli($host, $username_db, $password_db, $database);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the employer's username from the session or another reliable source
$employerUsername = $_SESSION['username'];

// If the form is submitted, update the employer's information
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect POST data and sanitize
    $numPeople = $conn->real_escape_string($_POST['num_people']);
    $roomType = $conn->real_escape_string($_POST['room_type']);
    $haveBaby = $_POST['have_baby'];
    $haveOlder = $_POST['have_older'];
    $languages = $_POST['languages'];
    $needCookHelper = $_POST['need_cook_helper'];
    $needExperience = $conn->real_escape_string($_POST['years_of_experience']);
	
	    // Calculate scores
    $personalScore = calculatePersonalScore($numPeople, $roomType, $haveBaby, $haveOlder, $languages, $needCookHelper);
    $expScore = calculateExpScore($needExperience);

    // Prepare an UPDATE statement
    $stmt = $conn->prepare("UPDATE employer_registration SET num_people=?, room_type=?, have_baby=?, have_older=?, languages=?, need_cook_helper=?, need_experience=?, need_personalScore=?, need_expScore=? WHERE username=?");
    
    // Bind parameters and execute the statement
     $stmt->bind_param("isisiiiiis", $numPeople, $roomType, $haveBaby, $haveOlder, $languages, $needCookHelper, $needExperience, $personalScore, $expScore, $employerUsername);
    
    if ($stmt->execute()) {
        $successMessage = "Your account has been updated successfully.";
    } else {
        $errorMessage = "Error updating account: " . $stmt->error;
    }
    
    // Close statement
    $stmt->close();
}

// Functions to calculate scores
function calculatePersonalScore($numPeople, $roomType, $haveBaby, $haveOlder, $languages, $needCookHelper) {
    $score1 = 0;
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

    return $score1;
}

function calculateExpScore($needExperience) {
    $score2 = 0;
	
	if ($_POST['years_of_experience'] == 0) {
    $score2 = 0;
	} elseif ($_POST['years_of_experience'] >= 1 && $_POST['years_of_experience'] <= 3) {
		$score2 = 3;
	} elseif ($_POST['years_of_experience'] > 3 && $_POST['years_of_experience'] <= 5) {
		$score2 = 5;
	} elseif ($_POST['years_of_experience'] > 5) {
		$score2 = 10;
	}
	
    return $score2;
}

// Attempt to retrieve current information of the employer
if ($stmt = $conn->prepare("SELECT num_people, room_type, have_baby, have_older, languages, need_cook_helper, need_experience FROM employer_registration WHERE username=?")) {
    $stmt->bind_param("s", $employerUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $employerData = $result->fetch_assoc();
    } else {
        $errorMessage = "Failed to retrieve account details or no such user exists.";
    }
    
    $stmt->close();
} else {
    $errorMessage = "Failed to prepare the statement for retrieving account details.";
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit employer Profile</title>
    <!-- Include additional CSS and JavaScript files as needed -->
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
    <h1>Edit Your Profile</h1>
	<a href="employer_homepage.php">Back</a>
    
    <?php if (!empty($successMessage)) echo "<div class='success-message'>$successMessage</div>"; ?>
    <?php if (!empty($errorMessage)) echo "<div class='error-message'>$errorMessage</div>"; ?>
    
    <?php if (!empty($employerData)): ?>
	
<div style="width: 100%; display: flex; flex-direction: row; justify-content: center; align-content: center; padding: 20px 20px;">
    <form action="employer_edit.php" method="POST" style="min-width: 500px; padding: 20px 20px; background-color: #fff; border-radius: 10px;">
        <label for="num_people">Number of People in Household:</label><br>
        <input type="number" name="num_people" id="num_people" value="<?php echo htmlspecialchars($employerData['num_people']); ?>" required><br><br>

        <label for="room_type">Room for Helper:</label><br>
        <select name="room_type" id="room_type" required>
            <option value="single" <?php echo ($employerData['room_type'] === 'single') ? 'selected' : ''; ?>>Single Room</option>
            <option value="shared" <?php echo ($employerData['room_type'] === 'shared') ? 'selected' : ''; ?>>Shared Room</option>
            <option value="noroom" <?php echo ($employerData['room_type'] === 'noroom') ? 'selected' : ''; ?>>No Room</option>
        </select><br><br>

        <label for="have_baby">Do you have a baby?</label><br>
		<select name="have_baby" id="have_baby" required>
            <option value="yes" <?php echo ($employerData['have_baby'] === 'yes') ? 'selected' : ''; ?>>Yes</option>
            <option value="no" <?php echo ($employerData['have_baby'] === 'no') ? 'selected' : ''; ?>>No</option>
        </select><br><br>

        <label for="have_older">Do you have elderly?</label><br>
		<select name="have_older" id="have_older" required>
            <option value="yes" <?php echo ($employerData['have_older'] === 'yes') ? 'selected' : ''; ?>>Yes</option>
            <option value="no" <?php echo ($employerData['have_older'] === 'no') ? 'selected' : ''; ?>>No</option>
        </select><br><br>        
		
		<label for="languages">Languages Spoken:</label><br>
		<select name="languages" id="languages" required>
            <option value="yes" <?php echo ($employerData['languages'] === 'fluent') ? 'selected' : ''; ?>>Fluent</option>
            <option value="no" <?php echo ($employerData['languages'] === 'common') ? 'selected' : ''; ?>>Common</option>
			<option value="no" <?php echo ($employerData['languages'] === 'fair') ? 'selected' : ''; ?>>Fair</option>
        </select><br><br>   

        <label for="need_cook_helper">Do you need a helper who can cook?</label><br>
		<select name="need_cook_helper" id="need_cook_helper" required>
            <option value="yes" <?php echo ($employerData['need_cook_helper'] === 'yes') ? 'selected' : ''; ?>>Yes</option>
            <option value="no" <?php echo ($employerData['need_cook_helper'] === 'no') ? 'selected' : ''; ?>>No</option>
        </select><br><br> 
		
        <label for="years_of_experience">Years of Experience Required:</label><br>
        <input type="number" name="years_of_experience" id="years_of_experience" value="<?php echo htmlspecialchars($employerData['need_experience']); ?>" required><br><br>

        <input type="submit" value="Update Profile">
    </form>
</div>
    <?php else: ?>
    <p>Could not load employer data. Please try again or contact support if the problem persists.</p>
    <?php endif; ?>

    <!-- Include footer and other templates as needed -->
</body>
</html>

