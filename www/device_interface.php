<?php 
/* ----------------------------------------------------------------------- *
 *
 *   Copyright (C) 2016, Simon Adam, Markus Dullnig, Paul Soelder
 *   All rights reserved.
 *
 *   This file is part of the indoor air quality server
 *   and is made available under the terms of the BSD 3-Clause Licence.
 *   A full copy of the licence can be found in the COPYING file.
 *
 * ----------------------------------------------------------------------- */

// database settings
$host = "localhost";
$user = "iaq2015";
$password = "iaqdb";
$database = "iaq2015";

// host-IP whitelisting
// e.g. $allowed_hosts = "10.10.120.*";
$allowed_hosts = "*.*.*.*";

if($_SERVER["REQUEST_METHOD"] != "GET") {
	exit();
}

if(fnmatch($allowed_hosts, $_SERVER['REMOTE_ADDR']) === FALSE) {
	echo $_SERVER['REMOTE_ADDR'];
	die('Error: Your IP is not whitelisted in device_interface.php');
}

if(empty($_GET['action'])) {
	header('Location: index.php');
	exit();
}

else if($_GET['action'] == "log" && !empty($_GET['room'])) {
	if(empty($_GET['error']) || $_GET['error'] == "0") {
		$error = 0;
		$room = $_GET['room'];
		$co2 = $_GET['co2'];
		$temp = $_GET['temp'];
		$rh = $_GET['rh'];
		$led_state = $_GET['led_state'];
	}

	else {
		$error = $_GET['error'];
		$room = $_GET['room'];
	}

	// search if room already exists and get id
	$sql = "SELECT id FROM id_table WHERE room=?";

	$con = new mysqli($host, $user, $password, $database);

	if($con->connect_error) {
		die("Connect error: (" . $con->connect_errno . ")" .
				$con->connect_error);
	}

	if(!$stmt = $con->prepare($sql)) {
		die("1MySQL error: (" . $con->errno . ")" . $con->error);
	}

	if(!$stmt->bind_param("s", $room)) {
		die("2MySQL error: (" . $stmt->errno . ")" . $stmt->error);
	}

	if(!$stmt->execute()) {
		die("3MySQL error: (" . $stmt->errno . ")" . $stmt->error);
	}

	$stmt->store_result();

	$stmt->bind_result($id_f);

	// room exists
	if($stmt->num_rows > 0) {
		while($stmt->fetch()) {
			$id = $id_f;
		}
	}

	// room does not exist -> create room
	else {
		$sql = "INSERT INTO id_table room VALUES (?)";

		if($con->connect_error) {
			die("Connect error: (" . $con->connect_errno . ")" .
					$con->connect_error);
		}

		if(!$stmt = $con->prepare($sql)) {
			die("4MySQL error: (" . $con->errno . ")" . $con->error);
		}

		if(!$stmt->bind_param("s", $room)) {
			die("5MySQL error: (" . $stmt->errno . ")" . $stmt->error);
		}

		if(!$stmt->execute()) {
			die("6MySQL error: (" . $stmt->errno . ")" . $stmt->error);
		}

		$stmt->close();

		// get id of newly created room
		$sql = "SELECT LAST_INSERT_ID()";

		$result = $con->query($sql);

		if($result->num_rows > 0) {
			while($row = $result->fetch_array(MYSQLI_NUM)) {
				$id = $row[0];
			}
		}

		$con->close();
	}

	// push data into the database
	if($error == 0) {
		$sql = "INSERT INTO data_table (room_id, co2, temp, rh, led_state, " .
				"timestamp) values (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

		if(!$stmt = $con->prepare($sql)) {
			die("7MySQL error: (" . $con->errno . ")" . $con->error);
		}

		echo "$id\n$co2\n$temp\n$rh\n$led_state";

		if(!$stmt->bind_param("iiddi", $id, $co2, $temp, $rh, $led_state)) {
			die("8MySQL error: (" . $stmt->errno . ")" . $stmt->error);
		}

		if(!$stmt->execute()) {
			die("9MySQL error: (" . $stmt->errno . ")" . $stmt->error);
		}

		$stmt->close();
	}
	else {
		$sql = "INSERT INTO data_table (room_id, error, timestamp) values (?, ".
				"?, CURRENT_TIMESTAMP)";

		if(!$stmt = $con->prepare($sql)) {
			die("10MySQL error: (" . $con->errno . ")" . $con->error);
		}

		if(!$stmt->bind_param("ii", $id, $error)) {
			die("11MySQL error: (" . $stmt->errno . ")" . $stmt->error);
		}

		if(!$stmt->execute()) {
			die("12MySQL error: (" . $stmt->errno . ")" . $stmt->error);
		}

		$stmt->close();
	}
} // action==log
?>
