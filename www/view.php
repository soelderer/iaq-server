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

if($_SERVER["REQUEST_METHOD"] != "GET") {
	exit();
}

if(empty($_GET['id'])) {
	header('Location: index.php');
	exit();
}

else {
	$id = htmlEntities($_GET['id'], ENT_QUOTES);

	$con = new mysqli($host, $user, $password, $database);

	if($con->connect_error) {
		die("Connect error: (" . $con->connect_errno . ")" .
				$con->connect_error);
	}

	// Raum aus id_table

	if(!$stmt = $con->prepare("SELECT room FROM id_table WHERE id=?")) {
		die("MySQL error: (" . $con->errno . ")" . $con->error);
	}

	if(!$stmt->bind_param("i", $id)) {
		die("MySQL error: (" . $stmt->errno . ")" . $stmt->error);
	}

	if(!$stmt->execute()) {
		die("MySQL error: (" . $stmt->errno . ")" . $stmt->error);
	}


	$stmt->store_result();

	$stmt->bind_result($room_f);

	if($stmt->num_rows > 0) {
		while($stmt->fetch()) {
			$room = htmlEntities($room_f, ENT_QUOTES);
		}
	}


	else {
		echo "<p>Keine Datens&auml;tze.</p>";
		echo "<a href=\"index.php\">Zur&uuml;ck</a>";
	}

	$stmt->free_result();
	$stmt->close();

	// Logdaten aus data_table

	if(!$stmt = $con->prepare("SELECT co2, temp, rh, led_state, error FROM " .
			"data_table WHERE id=? ORDER BY timestamp DESC LIMIT 1")) {
		die("MySQL error: (" . $con->errno . ")" . $con->error);
	}

	if(!$stmt->bind_param("i", $id)) {
		die("MySQL error: (" . $stmt->errno . ")" . $stmt->error);
	}

	if(!$stmt->execute()) {
		die("MySQL error: (" . $stmt->errno . ")" . $stmt->error);
	}

	$stmt->store_result();

	$stmt->bind_result($co2_f, $temp_f, $rh_f, $led_state_f, $error_f);

	if($stmt->num_rows > 0) {
		while($stmt->fetch()) {
			$co2 = htmlEntities($co2_f, ENT_QUOTES);
			$temp = htmlEntities($temp_f, ENT_QUOTES);
			$rh = htmlEntities($rh_f, ENT_QUOTES);
			$led_state = htmlEntities($led_state_f, ENT_QUOTES);
			$error = htmlEntities($error_f, ENT_QUOTES);
		}
	}

	else {
		echo "<p>Keine Datens&auml;tze.</p>";
		echo "<a href=\"index.php\">Zur&uuml;ck</a>";
	}

	$stmt->free_result();
	$stmt->close();
	$con->close();
}

// Wurde auf Link gedrueckt?
if(!empty($_GET['printdata']) && !empty($_GET['scale'])) {
	$printdata = urlencode($_GET["printdata"]);
	$scale = urlencode($_GET["scale"]);
}
// Wenn nicht -> Default Wert
else {
	$printdata = 'co2';
	$scale = 'day';
}

$id = urlencode($id);

?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Luftg&uumlteampel<?php echo " - $room"; ?></title>

		<style type="text/css">
			a {
				font-size: 0.6cm;
				}
		</style>
	</head>

	<body>
		<div style="float: left;"><a href="index.php"
				style="font-size: medium;">Zur&uumlck</a></div>

		<div style="text-align:right;">
			<a href="csv.php?id=<?php echo "$id"; ?>" target="_blank"
					style="font-size: medium;">Datenbank herunterladen</a>
		</div>

		<h1 style="text-align:center;">LUFTG&UumlTEAMPEL<?php
				echo " - $room"; ?></h1>

		<div style="float:left;">
			<a href="view.php?printdata=co2&scale=<?php echo "$scale&id=$id";
					?>">CO2</a>
			<a href="view.php?printdata=temp&scale=<?php
					echo "$scale&id=$id";?>">Temperatur</a>
			<a href="view.php?printdata=rh&scale=<?php
					echo "$scale&id=$id";?>">Luftfeuchtigkeit</a>
			<br />
			<a href="view.php?printdata=<?php
					echo "$printdata&id=$id";?>&scale=day">Tag</a>
			<a href="view.php?printdata=<?php
					echo "$printdata&id=$id";?>&scale=week">Woche</a>
			<br />
			<img src="graph.php?<?php
					echo "printdata=$printdata&scale=$scale&id=$id";
					?>" width="900" height="800" />
		</div>

		<div style="text-align:center;">
			<div style="font-size:74px; font-weight:bold;"><?php
					echo "$co2 ppm CO2";?><sub></sub></div>
			<div style="font-size:62px;"><?php
					echo "$rh&#37;RH";?></div>
			<div style="font-size:62px;"><?php
					echo "$temp&deg;C";?></div>
		</div>

		<div style="text-align:center">
			<br /><br /><br />
			<img <?php
				switch ($led_state) {
					case 0:
						echo "src=\"img/trafficlight_off.svg\"";
						break;
					case 1:
						echo "src=\"img/trafficlight_green.svg\"";
						break;
					case 2:
						echo "src=\"img/trafficlight_yellow.svg\"";
						break;
					case 3:
						echo "src=\"img/trafficlight_red.svg\"";
						break;
				}
			?> width="150" height="375" />
		</div>
	</body>
</html>
