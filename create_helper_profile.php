<?php
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
} else {
    // Handle the case when agency ID is not found
    header('Location: index.php');
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $name = $conn->real_escape_string($_POST['name']);
    $age = intval($_POST['age']);
    $numPeople = intval($_POST['num_people']);
    $roomType = $_POST['room_type'];
    $haveBaby = $_POST['have_baby'] ;
    $haveOlder = $_POST['have_older'];
	$languages = $_POST['languages'];
    $needCookHelper = $_POST['need_cook_helper'];
    $yearsOfExperience = intval($_POST['years_of_experience']);
	$totalContracts = intval($_POST['total_contracts']);
	$lastContractDuration = $_POST['last_contract_duration'];

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

	$score1 += isset($_FILES['document']['name']) ? 10 : 0;

	$score1 += isset($_FILES['certificate']['name']) ? 10 : 0;

	// Calculate score based on total working years
	if ($yearsOfExperience == 0) {
		$score2 += 0;
	} elseif ($yearsOfExperience >= 1 && $yearsOfExperience <= 3) {
		$score2 += 1;
	} elseif ($yearsOfExperience > 3 && $yearsOfExperience <= 5) {
		$score2 += 3;
	} elseif ($yearsOfExperience > 5) {
		$score2 += 5;
	}

	// Calculate score based on total contracts
	if ($totalContracts == 0) {
		$score2 += 0;
	} elseif ($totalContracts >= 1 && $totalContracts <= 3) {
		$score2 += 1;
	} elseif ($totalContracts > 3 && $totalContracts <= 5) {
		$score2 += 1;
	} elseif ($totalContracts > 5) {
		$score2 += 1;
	}

	// Calculate score based on last contract duration
	if ($lastContractDuration < 1) {
		$score2 -= 20;
	} elseif ($lastContractDuration == 1) {
		$score2 += 1;
	} elseif ($lastContractDuration == 2) {
		$score2 += 2;
	} elseif ($lastContractDuration == 3) {
		$score2 += 3;
	} elseif ($lastContractDuration == 4) {
		$score2 += 4;
	} elseif ($lastContractDuration == 5) {
		$score2 += 5;
	} elseif ($lastContractDuration > 6) {
		$score2 += 10;
	}

	// Calculate score based on the ratio of total working years to total contracts
	if ($totalContracts > 0) {
		$ratio = $yearsOfExperience / $totalContracts;
		if ($ratio >= 0 && $ratio < 1) {
			$score2 -= 20;
		} elseif ($ratio >= 1 && $ratio < 2) {
			$score2 += 1;
		} elseif ($ratio >= 2 && $ratio < 3) {
			$score2 += 2;
		} elseif ($ratio >= 3 && $ratio < 4) {
			$score2 += 3;
		} elseif ($ratio >= 4 && $ratio < 5) {
			$score2 += 4;
		} elseif ($ratio >= 5 && $ratio < 6) {
			$score2 += 5;
		} elseif ($ratio >= 6) {
			$score2 += 10;
		}
	} else {
		// Handle the case when $totalContracts is zero
		// You can set a default value for $ratio or handle it accordingly
		// For example:
		$ratio = 0; // Set the ratio to 0 or any other default value
		// Rest of the code...
	}


    // Validate and sanitize form data as needed

    // Insert the helper profile into the database
    $insertSql = "INSERT INTO helper_profiles (name, age, num_people, room_type, have_baby, have_older, languages, need_cook_helper, years_of_experience, total_contracts, last_contract_duration, agency_id, personalScore, expScore) 
                  VALUES ('$name', '$age', '$numPeople', '$roomType', '$haveBaby', '$haveOlder', '$languages', '$needCookHelper', '$yearsOfExperience', '$totalContracts', '$lastContractDuration', '$agencyId', '$score1', '$score2')";

    if ($conn->query($insertSql) === TRUE) {
        // Get the newly created helper profile ID
        $helperProfileId = $conn->insert_id;

        // Initialize an array to keep track of uploaded files
        $uploadedFiles = array(
            'photo' => null,
            'document' => null,
            'certificate' => null
        );

        // Define directories
        $directories = array(
            'photo' => "helper_photos/",
            'document' => "helper_files/",
            'certificate' => "helper_certificates/"
        );

        // Define allowed file types
        $allowedExtensions = array(
            'photo' => array('jpg', 'jpeg', 'png', 'gif'),
            'document' => array('pdf', 'doc', 'docx'),
            'certificate' => array('pdf', 'doc', 'docx')
        );

        // Process each file upload
        foreach ($uploadedFiles as $fileKey => &$filePath) {
            if (isset($_FILES[$fileKey]['name']) && $_FILES[$fileKey]['error'] == UPLOAD_ERR_OK) {
                // Get file info
                $tempName = $_FILES[$fileKey]['tmp_name'];
                $fileName = basename($_FILES[$fileKey]['name']);
                $fileSize = $_FILES[$fileKey]['size'];
                $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Check if file type is allowed
                if (!in_array($fileType, $allowedExtensions[$fileKey])) {
                    echo "Error: Only " . implode(", ", $allowedExtensions[$fileKey]) . " files are allowed for $fileKey.";
                    exit;
                }

                // Check file size (5MB for documents/certificates, 500KB for photos)
                $sizeLimit = ($fileKey == 'photo') ? 500000 : 5000000;
                if ($fileSize > $sizeLimit) {
                    echo "Error: File is too large.";
                    exit;
                }

                // Create unique file name toprevent conflicts
                $newFileName = $directories[$fileKey] . uniqid() . '.' . $fileType;
                
                // Check if file already exists
                if (file_exists($newFileName)) {
                    echo "Error: File already exists.";
                    exit;
                }

                // Attempt to move the file
                if (move_uploaded_file($tempName, $newFileName)) {
                    // File uploaded successfully
                    $filePath = $newFileName;
                } else {
                    echo "Error: There was a problem uploading your file.";
                    exit;
                }
            }
        }

        // Update the helper profile with the paths of uploaded files
        $updateSql = "UPDATE helper_profiles SET photo_path = '".$conn->real_escape_string($uploadedFiles['photo'])."',
                                                document_path = '".$conn->real_escape_string($uploadedFiles['document'])."',
                                                certificate_path = '".$conn->real_escape_string($uploadedFiles['certificate'])."' 
                                                WHERE id = '$helperProfileId'";
        $conn->query($updateSql);

        // Redirect to the agency homepage
        header('Location: agency_homepage.php');
        exit;
    } else {
        // Handle error when insertion fails
        echo "Error: " . $insertSql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Helper Profile</title>
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
    <h1>Create Helper Profile</h1>

    <?php if (isset($errorMessage)) { ?>
        <p><?php echo $errorMessage; ?></p>
    <?php } ?>
	<div style="width: 100%; display: flex; flex-direction: row; justify-content: center; align-content: center; padding: 20px 20px;">
    <form action="create_helper_profile.php" method="POST" enctype="multipart/form-data" style="min-width: 500px; padding: 20px 20px; background-color: #fff; border-radius: 10px;">
        
		<label for="photo">Upload Helper Photo:</label><br>
        <input type="file" name="photo" id="photo" accept="image/*" required><br><br>
		
		<label for="name">Helper Name:</label><br>
        <input type="text" name="name" id="name" required><br><br>

        <label for="age">Age:</label><br>
        <input type="number" name="age" id="age" required><br><br>

        <label for="num_people">Prefer Live with No. People</label><br>
        <input type="number" name="num_people" id="num_people" required><br><br>

        <label for="room_type">Prefer Room Type:</label><br>
        <select id="room_type" name="room_type" required>
            <option value="single">Single Room</option>
            <option value="shared">Shared Room</option>
			<option value="noroom">No Room</option>
        </select><br><br>

        <label for="have_baby">Take care with Baby:</label><br>
        <select id="have_baby" name="have_baby" required>
            <option value="yes">Yes</option>
            <option value="no">No</option>
        </select><br><br>

        <label for="have_older">Take care with Older Person:</label><br>
        <select id="have_older" name="have_older" required>
            <option value="yes">Yes</option>
            <option value="no">No</option>
        </select><br><br>
		
		<label for="languages">English Level:</label><br>
        <select id="languages" name="languages" required>
            <option value="fluent">Fluent</option>
            <option value="common">Common</option>
			<option value="fair">Fair</option>
        </select><br><br>

		<label for="document">Upload Qulification Document:</label><br>
        <input type="file" name="document" id="document" accept=".pdf,.docx,.doc"><br><br>

        <label for="need_cook_helper">Know How to Cook:</label><br>
        <select id="need_cook_helper" name="need_cook_helper" required>
            <option value="yes">Yes</option>
            <option value="no">No</option>
        </select><br><br>

		<label for="certificate">Upload Cooking Certificate:</label><br>
		<input type="file" name="certificate" id="certificate" accept=".pdf,.docx,.doc"><br><br>

        <label for="years_of_experience">Years of Experience:</label><br>
        <input type="number" name="years_of_experience" id="years_of_experience" required><br><br>

		<label for="total_contracts">Total Contracts:</label><br>
		<input type="number" name="total_contracts" id="total_contracts" required><br><br>

		<label for="last_contract_duration">Last Contract Duration (in years; if not enough 1 year, please enter "0"):</label><br>
		<input type="number" step="any" name="last_contract_duration" id="last_contract_duration" required><br><br>

        <input type="submit" name="submit" value="Create Profile">
    </form>
	</div>
</body>
</html>