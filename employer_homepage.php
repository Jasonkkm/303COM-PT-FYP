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

// Check if the employer is logged in
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] !== 'employer' || !isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$employerUsername = $_SESSION['username'];

// Connect to the database
require_once('dbconfig.php');
$conn = new mysqli($host, $username_db, $password_db, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT need_personalScore, need_expScore FROM employer_registration WHERE username = '$employerUsername'";
$result = $conn->query($sql);

$minAdjustment = -10;
$maxAdjustment = 25;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['score_range'])) {
        list($minAdjustment, $maxAdjustment) = explode(',', $_POST['score_range']);
    }
}

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $employerTotalScore = $row['need_personalScore'] + $row['need_expScore'];
    $minScore = $employerTotalScore + $minAdjustment;
    $maxScore = $employerTotalScore + $maxAdjustment;

    $sql = "SELECT hp.*, ar.agency_username, ar.agency_tel, hp.document_path, hp.certificate_path FROM helper_profiles hp
    INNER JOIN agency_registration ar ON hp.agency_id = ar.agency_id
    WHERE (personalScore + expScore) BETWEEN $minScore AND $maxScore";
    $result = $conn->query($sql);
} else {
    echo 'Error: employer score not found.';
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>employer Homepage</title>
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
	<div style="wdith: 100%; display: flex; justify-content: space-between; align-items: center;">
		<div>
		<h1>Welcome to the employer Homepage, <?php echo $_SESSION['username']; ?></h1>
		
		<p><a href="employer_edit.php">Edit Profile</a></p>
		</div>
		<div>
		<form action="logout.php" method="POST">
			<button type="submit" name="logout">Logout</button>
			<input type="hidden" name="last_activity" value="<?php echo time(); ?>">
		</form>
		</div>
	</div>
		<div>
        <form method="POST" action="">
            <label for="score_range">AI Range:</label>
            <select name="score_range" id="score_range">
                <option value="-10,25">Match</option>
                <option value="-20,35">Wide</option>
                <option value="-30,45">Large</option>
                <option value="-40,55">Huge</option>
            </select>
            <button type="submit" name="submit">Adjust</button>
        </form>
		</div><br>
	
		<div class="search_bar" style="display: flex;">
			<div>
				<label for="search_num_people">Number of People:</label>
				<select name="search_num_people" id="search_num_people">
					<option value="all">All</option>
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="6">6+</option>
				</select>
			</div>
			<div>
				<label for="search_room_type">Room Type:</label>
				<select name="search_room_type" id="search_room_type">
					<option value="all">All</option>
					<option value="single">Single</option>
					<option value="shared">Shared</option>
					<option value="noroom">No Room</option>
				</select>
			</div>
			<div>
				<label for="search_have_baby">Have Baby:</label>
				<select name="search_have_baby" id="search_have_baby">
					<option value="all">All</option>
					<option value="yes">Yes</option>
					<option value="no">No</option>
				</select>
			</div>
			<div>
				<label for="search_have_older">Older:</label>
				<select name="search_have_older" id="search_have_older">
					<option value="all">All</option>
					<option value="yes">Yes</option>
					<option value="no">No</option>
				</select>
			</div>
			<div>
				<label for="search_languages">English Level:</label>
				<select name="search_languages" id="search_languages">
					<option value="all">All</option>
					<option value="fluent">Fluent</option>
					<option value="common">Common</option>
					<option value="fair">Fair</option>
				</select>
			</div>
			<div>
				<label for="search_need_cook_helper">Need Cook:</label>
				<select name="search_need_cook_helper" id="search_need_cook_helper">
					<option value="all">All</option>
					<option value="yes">Yes</option>
					<option value="no">No</option>
				</select>
			</div>
		<div>
			<label for="search_loyalty_level">Loyalty Level:</label>
			<select name="search_loyalty_level" id="search_loyalty_level">
				<option value="all">All</option>
				<option value="High">High</option>
				<option value="Low">Low</option>
			</select>
		</div>
			<!-- Add more select elements for other filters -->
		</div>

		<h2>Helper Profiles</h2>

		<?php if ($result->num_rows > 0) : ?>
			<table id="helper_profiles_table" style="width: 100%; min-width: 100%;">
				<thead>
				<tr>
					<th>Agency Company Name</th>
					<th>Agency Tel</th>
					<th>Photo</th>
					<th>Name</th>
					<th>Age</th>
					<th>Number of People</th>
					<th>Room Type</th>
					<th>Have Baby</th>
					<th>Have Older Person</th>
					<th>Languages</th>
					<th>Need Cooking Helper</th>
					<th>Years of Experience</th>
					<th>Loyalty Level</th>
					<th>Document</th>
					<th>Certificate</th>
				</tr>
				</thead>
		<?php while ($row = $result->fetch_assoc()) : ?>
			<tr data-num-people="<?php echo $row['num_people']; ?>" data-room-type="<?php echo $row['room_type']; ?>" data-have-baby="<?php echo $row['have_baby']; ?>" data-have-older="<?php echo $row['have_older']; ?>" data-languages="<?php echo $row['languages']; ?>" data-need-cook-helper="<?php echo $row['need_cook_helper']; ?>" data-loyalty-level="<?php echo $row['expScore'] >= 0 ? 'High' : 'Low'; ?>">
				<td><?php echo $row['agency_username']; ?></td>
				<td><?php echo $row['agency_tel']; ?></td>
				<td>
				<?php if ($row['photo_path']) { ?>
				<img src="<?php echo $row['photo_path']; ?>" alt="Helper Photo" width="60">
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
				<td><?php echo $row['need_cook_helper']; ?></td>
				<td><?php echo $row['years_of_experience']; ?></td>
				<td><?php echo $row['expScore'] >= 0 ? 'High' : 'Low'; ?></td>
				    <td>
						<?php if ($row['document_path']) { ?>
							<a href="<?php echo $row['document_path']; ?>" target="_blank">View Document</a>
						<?php } else { ?>
							No document available
						<?php } ?>
					</td>
					<td>
						<?php if ($row['certificate_path']) { ?>
							<a href="<?php echo $row['certificate_path']; ?>" target="_blank">View Certificate</a>
						<?php } else { ?>
							No certificate available
						<?php } ?>
					</td>
			</tr>
		<?php endwhile; ?>
			</table>
		<?php else : ?>
			<p>No matching helper profiles found.</p>
		<?php endif; ?>

		<script>
			// Get the select elements
			const selectNumPeople = document.getElementById('search_num_people');
			const selectRoomType = document.getElementById('search_room_type');
			const selectHaveBaby = document.getElementById('search_have_baby');
			const selectHaveOlder = document.getElementById('search_have_older');
			const selectLanguages = document.getElementById('search_languages');
			const selectNeedCookHelper = document.getElementById('search_need_cook_helper');
			const selectLoyaltyLevel = document.getElementById('search_loyalty_level');

			// Add more select elements as needed

			// Attach an event listener to the select elements
			selectNumPeople.addEventListener('change', filterProfiles);
			selectRoomType.addEventListener('change', filterProfiles);
			selectHaveBaby.addEventListener('change', filterProfiles);
			selectHaveOlder.addEventListener('change', filterProfiles);
			selectLanguages.addEventListener('change', filterProfiles);
			selectNeedCookHelper.addEventListener('change', filterProfiles);
			selectLoyaltyLevel.addEventListener('change', filterProfiles);
			// Add event listeners for other select elements

			function filterProfiles() {
				// Get the selected values from the select elements
				const filterNumPeople = selectNumPeople.value;
				const filterRoomType = selectRoomType.value;
				const filterHaveBaby = selectHaveBaby.value;
				const filterHaveOlder = selectHaveOlder.value;
				const filterLanguages = selectLanguages.value;
				const filterNeedCookHelper = selectNeedCookHelper.value;
				const filterLoyaltyLevel = selectLoyaltyLevel.value;
				// Get selected values from other select elements

				// Get all the helper profile rows
				const profileRows = document.querySelectorAll('#helper_profiles_table tbody tr');

				// Loop through each profile row and apply the filters
				for (const row of profileRows) {
					const numPeople = row.dataset.numPeople;
					const roomType = row.dataset.roomType;
					const haveBaby = row.dataset.haveBaby;
					const haveOlder = row.dataset.haveOlder;
					const languages = row.dataset.languages;
					const needCookHelper = row.dataset.needCookHelper;
					const loyaltyLevel = row.dataset.loyaltyLevel;
					// Get other relevant data attributes from the row

					// Check if the row matches the selected filter values
					const isMatch =
						(filterNumPeople === 'all' || numPeople === filterNumPeople) &&
						(filterRoomType === 'all' || roomType === filterRoomType) &&
						(filterHaveBaby === 'all' || haveBaby === filterHaveBaby) &&
						(filterHaveOlder === 'all' || haveOlder === filterHaveOlder) &&
						(filterLanguages === 'all' || languages === filterLanguages) &&
						(filterNeedCookHelper === 'all' || needCookHelper === filterNeedCookHelper)&&
						(filterLoyaltyLevel === 'all' || loyaltyLevel === filterLoyaltyLevel);

					// Add conditions for other relevant filters

					// Show or hide the row based on the filter match
					row.style.display = isMatch ? 'table-row' : 'none';
				}
			}
		</script>
	</body>
</html>