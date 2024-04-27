<?php

// Set the inactivity timeout period in seconds (3 minutes in this example)
$timeoutSeconds = 180;

// Check if the last activity timestamp is set in the session
if (isset($_SESSION['last_activity'])) {
    // Calculate the time difference between the current time and the last activity time
    $inactiveSeconds = time() - $_SESSION['last_activity'];

    // Check if the inactive time exceeds the timeout period
    if ($inactiveSeconds >= $timeoutSeconds) {
        // Destroy the session and redirect to the login page
        session_destroy();
        header('Location: index.php');
        exit;
    }
}

// Update the last activity timestamp in the session
$_SESSION['last_activity'] = time();

session_start();

// Check if the agency is logged in
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] !== 'agency' || !isset($_SESSION['username'])) {
    // Redirect to the login page or display an error message
    header('Location: index.php');
    exit;
}

// Get the agency username
$agencyUsername = $_SESSION['username'];

// Connect to the database
require_once('dbconfig.php');
$conn = new mysqli($host, $username_db, $password_db, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve the agency ID from the database
$sql = "SELECT agency_id FROM agency_registration WHERE username = '$agencyUsername'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $agencyId = $row['agency_id'];

    // Retrieve helper profiles specific to the agency from the database
    $sql = "SELECT * FROM helper_profiles WHERE agency_id = '$agencyId'";
    $result = $conn->query($sql);
} else {
    // Handle the case when agency ID is not found
    // Redirect to the login page or display an error message
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Agency Homepage</title>
	
	<style>


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
	<div style="wdith: 100%; display: flex; justify-content: space-between; align-items: center;">
		<div>
		<h1>Welcome to the Agency Homepage, <?php echo $_SESSION['username']; ?></h1>
		</div>
		<div style="height: 10px;">
		<form action="logout.php" method="POST">
			<button type="submit" name="logout">Logout</button>
			<input type="hidden" name="last_activity" value="<?php echo time(); ?>">
		</form>
		</div>
	</div>

    <h2>Helper Profiles</h2>
	<a href="create_helper_profile.php">Create Helper Profile</a><br><br>

    <?php if ($result->num_rows > 0) : ?>
        <table style="width: 100%; min-width: 100%;">
            <tr>
                <th>Photos</th>
				<th>Helper Name</th>
                <th>Age</th>
                <th>Live with No. People</th>
                <th>Room Type</th>
                <th>With Baby</th>
                <th>With Older</th>
                <th>Languages Level</th>
				<th>Qualification</th>
				<th>Cooking</th>
				<th>Certificate</th>
                <th>Experience (Year)</th>
				<th>Total Contracts</th>
				<th>Last Contract (Year)</th>
				<th></th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>					
					<td>
                        <?php if ($row['photo_path']) { ?>
                            <img src="<?php echo $row['photo_path']; ?>" alt="Helper Photo" width="100">
                        <?php } else { ?>
                            No photo available
                        <?php } ?>
                    </td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['age']; ?></td>
                    <td><?php echo $row['num_people']; ?></td>
                    <td><?php echo $row['room_type']; ?></td>
                    <td><?php echo $row['have_baby']; ?></td>
                    <td><?php echo $row['have_older']; ?></td>
					<td><?php echo $row['languages']; ?></td>					
					<td>
						<?php if (isset($row['document_path']) && !empty($row['document_path'])) : ?>
							<a href="<?php echo $row['document_path']; ?>" target="_blank">View Document</a>
						<?php else : ?>
							No document available.
						<?php endif; ?>
					</td>
                    <td><?php echo $row['need_cook_helper']; ?></td>
					<td>
						<?php if (isset($row['certificate_path']) && !empty($row['certificate_path'])) : ?>
							<a href="<?php echo $row['certificate_path']; ?>" target="_blank">View Document</a>
						<?php else : ?>
							No certificate document.
						<?php endif; ?>
					</td>
                    <td><?php echo $row['years_of_experience']; ?></td>
					<td><?php echo $row['total_contracts']; ?></td>
					<td><?php echo $row['last_contract_duration']; ?></td>
            <td>
                <a href="delete_helper_profile.php?id=<?php echo $row['id']; ?>">Delete</a>
            </td>

                </tr>
            <?php endwhile; ?>
        </table>
    <?php else : ?>
        <p>No helper profiles found.</p>
    <?php endif; ?>

</body>
</html>