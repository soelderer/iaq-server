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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Luftg&uuml;teampel</title>
</head>
<body>

<h1 style="text-align: center;">LUFTG&Uuml;TEAMPEL</h1>

<form method="get" action="view.php">
<?php

echo "\t<select name='id'>\n";

$con = new mysqli($host, $user, $password, $database);

if($con->connect_error) {
	die("Connect error: (" . $con->connect_errno . ")" .
			$con->connect_error);
}

else {
	$sql = "SELECT id, room FROM id_table ORDER BY room ASC";

	$result = $con->query($sql);

	if($result->num_rows > 0) {
		while ($row = $result->fetch_array(MYSQLI_NUM)) {
			$id = htmlEntities($row[0], ENT_QUOTES);
			$room = htmlEntities($row[1], ENT_QUOTES);

			echo "\t\t<option value=\"$id\">$room</option>\n";
		}
	}

	$con->close();

	echo "\t</select>\n";
}
?>

	<button type="submit">Anzeigen</button>
</form>

</body>
</html>
