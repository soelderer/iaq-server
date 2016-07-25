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

require_once('jpgraph/src/jpgraph.php');
require_once('jpgraph/src/jpgraph_line.php');
require_once('jpgraph/src/jpgraph_date.php');
require_once('jpgraph/src/jpgraph_plotline.php');

// Wurde auf Link gedrueckt?
if(!empty($_GET['printdata']) && !empty($_GET['scale'])) {
	$printdata = $_GET["printdata"];
	$scale = $_GET["scale"];
}
// Wenn nicht -> Standardwert
else {
	$printdata = 'co2';
	$scale = 'day';
}

if(empty($_GET['id'])) {
	header('Location: index.php');
	exit();
}

$id = $_GET['id'];

$con = new mysqli($host, $user, $password, $database);

if($con->connect_error) {
	die("Connect error: (" . $con->connect_errno . ")" .
			$con->connect_error);
}

// Schwellwerte aus id_table

$sql = "";
switch($printdata) {
	case 'co2':
		$sql = "SELECT co2_threshold_red, co2_threshold_yellow FROM ".
				" id_table WHERE id=?";
		break;
	case 'temp':
		$sql = "SELECT temp_threshold_red, temp_threshold_yellow FROM ".
				" id_table WHERE id=?";
		break;
	case 'rh':
		$sql = "SELECT rh_threshold_red, rh_threshold_yellow FROM ".
				" id_table WHERE id=?";
		break;
}

// momentan nur fuer CO2
if($printdata == 'co2') {
	if(!$stmt = $con->prepare($sql)) {
		die("MySQL error: (" . $mysqli->errno . ")" . $mysqli->error);
	}

	if(!$stmt->bind_param("i", $id)) {
		die("MySQL error: (" . $stmt->errno . ")" . $stmt->error);
	}

	if(!$stmt->execute()) {
		die("MySQL error: (" . $stmt->errno . ")" . $stmt->error);
	}

	$stmt->store_result();

	$stmt->bind_result($th_red_f, $th_yellow_f);

	if($stmt->num_rows > 0) {
		while($stmt->fetch()) {
			$th_red = $th_red_f;
			$th_yellow = $th_yellow_f;
		}
	}

	else {
		exit();
	}
}

// create an sql query variable
// added an extra condition to the querys
// changded the table to the table in the current database
// removed alias

/*--------------------------------- CO2-Graph --------------------------------*/
$sql = "";

switch($printdata) {
	case 'co2':
		switch($scale) {
			case 'day':
				$sql = "SELECT UNIX_TIMESTAMP(timestamp), co2 FROM data_table" .
						" WHERE id=? AND timestamp >=" .
						" Date_Sub(CURRENT_TIMESTAMP(), Interval 24 HOUR)" .
						" ORDER BY timestamp";
				// Format in dem das Datum ausgegeben werden soll
				$date = 'H:i';
				// Position der x-Achsenbeschriftung
				$xtitlepos = '40';
				break;

			case 'week':
				$sql = "SELECT UNIX_TIMESTAMP(timestamp), co2 FROM data_table" .
						" WHERE id=? AND timestamp >=" .
						" Date_Sub(CURRENT_TIMESTAMP(), Interval 7 DAY)" .
						" ORDER BY timestamp";
				// Format in dem das Datum ausgegeben werden soll
				$date = 'j. M H:i';
				// Position der x-Achsenbeschriftung
				$xtitlepos = '80';
				break;
		}

		$row_index = 'co2';
		$ytitle = 'CO2-Gehalt [ppm]';
		$fillcolor = 'navy@0.5';
		$titel = 'Zeitlicher Verlauf des CO2-Gehaltes';
		break;
/*---------------------------- Temperatur-Graph ------------------------------*/
	case 'temp':
		switch($scale) {
			case 'day':
				$sql = "SELECT UNIX_TIMESTAMP(timestamp), temp FROM data_table".
						" WHERE id=? AND timestamp >=" .
						" Date_Sub(CURRENT_TIMESTAMP(), Interval 24 HOUR)" .
						" ORDER BY timestamp";
				// Format in dem das Datum ausgegeben werden soll
				$date = 'H:i';
				// Position der x-Achsenbeschriftung
				$xtitlepos = '40';
				break;

			case 'week':
				$sql = "SELECT UNIX_TIMESTAMP(timestamp), temp FROM data_table".
						" WHERE id=? AND timestamp >=" .
						" Date_Sub(CURRENT_TIMESTAMP(), Interval 7 DAY)" .
						" ORDER BY timestamp";
				// Format in dem das Datum ausgegeben werden soll
				$date = 'j. M H:i';
				// Position der x-Achsenbeschriftung
				$xtitlepos = '80';
				break;
		}

		$row_index = 'temp';
		$ytitle = 'Temperatur [Grad Celsius]';
		$fillcolor = 'orange@0.5';
		$titel = 'Zeitlicher Verlauf der Temperatur';
		break;
/*------------------------------ Humidity-Graph ------------------------------*/
	case 'rh':
	switch($scale) {
		case 'day':
			$sql = "SELECT UNIX_TIMESTAMP(timestamp), rh FROM data_table" .
						" WHERE id=? AND timestamp >=" .
						" Date_Sub(CURRENT_TIMESTAMP(), Interval 24 HOUR)" .
						" ORDER BY timestamp";
			// Format in dem das Datum ausgegeben werden soll
			$date = 'H:i';
			// Position der x-Achsenbeschriftung
			$xtitlepos = '40';
			break;

		case 'week':
			$sql = "SELECT UNIX_TIMESTAMP(timestamp), rh FROM data_table" .
						" WHERE id=? AND timestamp >=" .
						" Date_Sub(CURRENT_TIMESTAMP(), Interval 7 DAY)" .
						" ORDER BY timestamp";
			// Format in dem das Datum ausgegeben werden soll
			$date = 'j. M H:i';
			// Position der x-Achsenbeschriftung
			$xtitlepos = '80';
			break;
	}

	$row_index = 'rh';
	$ytitle = 'Relative Luftfeuchtigkeit [%]';
	$fillcolor = 'cyan1@0.5';
	$titel = 'Zeitlicher Verlauf der relativen Luftfeuchtigkeit';
	break;
}

// do the query
if(!$stmt = $con->prepare($sql)) {
	die("MySQL error: (" . $mysqli->errno . ")" . $mysqli->error);
}

if(!$stmt->bind_param("i", $id)) {
	die("MySQL error: (" . $stmt->errno . ")" . $stmt->error);
}

if(!$stmt->execute()) {
	die("MySQL error: (" . $stmt->errno . ")" . $stmt->error);
}

$stmt->store_result();

$stmt->bind_result($xdata_f, $ydata_f);

if($stmt->num_rows > 0) {
	$i = 0;

	while($stmt->fetch()) {
		$xdata[$i] = $xdata_f;
		$ydata[$i] = $ydata_f;
		$i++;
	}
}

else {
	exit();
}

// neue Grafik erstellen
$graph = new graph(900, 800, 'auto');
// Ränder des Graphen
$graph->img->SetMargin(80, 10, 30, 0);
// Anit Aliasing ausschalten um die Linienstaerke veraendern zu koennen
// Anm: zeile 110 in jpgraph/src/gd_img_inc.php auskommentieren, da sonst
// bei fehlendem php5-gd eine fehlermeldung erscheint - obwohl antialiasing
// false gesetzt wurde:
// JpGraphError::RaiseL(25128);//('The function imageantialias() usw.
$graph->img->SetAntiAliasing(false);

$graph->SetScale('datint');

// Graphtitel
$graph->title->Set($titel);
// Schriftart von Graphtitel (größte bitmap schrift, fett)
$graph->title->SetFont(FF_FONT2, FS_BOLD);

// x-Achsen Titel und Ausrichtung
$graph->xaxis->SetTitle("Messzeitpunkt", "center");
// Abstand des Achsentitels zu den Labels der x-Werte
$graph->xaxis->SetTitleMargin($xtitlepos);
// Schriftart des x-Achsentitels
$graph->xaxis->title->SetFont(FF_FONT2, FS_BOLD);
// Winkel der Labels der x-Werte
$graph->xaxis->SetLabelAngle(90);
// Start und Stop Adjustierung der x-Achse auf 1 Sekunde
$graph->xaxis->scale->SetTimeAlign(SECADJ_1, SECADJ_1);
// Format des Datums
$graph->xaxis->scale->SetDateFormat($date);
// Grid auf der x-Achse
$graph->xgrid->Show();

// y-Achsen Titel
$graph->yaxis->title->Set($ytitle);
// Abstand des Achsentitels zu den Labels der y-Werte
$graph->yaxis->SetTitleMargin(50);
// Schriftart des x-Achsentitels
$graph->yaxis->title->SetFont(FF_FONT2, FS_BOLD);

// lineplot Objekt erstellt mit ydata und xdata als parameter
$lineplot = new LinePlot($ydata, $xdata);
// Füllfarbe von Graph
$lineplot->SetFillColor($fillcolor);

// Dem Graphen die Funktion hinzufuegen
$graph->Add($lineplot);

// Schwellwerte
// werden momentan nur fuer CO2 gesetzt
if(!empty($th_yellow) && !empty($th_red)) {
	// Horizontale Linie fuer Ampelstatus gelb erzeugen (staerke 2)
	$line1 = new PlotLine(HORIZONTAL, $th_yellow, "yellow", 2);
	// Legende erstellen
	$line1->SetLegend('GELB-Schwelle');

	// Horizontale Linie fuer Ampelstatus rot erzeugen (staerke 2)
	$line2 = new PlotLine(HORIZONTAL, $th_red, "red", 2);
	// Legende erstellen
	$line2->SetLegend('ROT-Schwelle');

	// Schwellwertlinien dem Graphen hinzufuegen
	$graph->AddLine($line1);
	$graph->AddLine($line2);
}

// Schriftart der Legende
$graph->legend->SetFont(FF_FONT2, FS_NORMAL);
// Liniensärke des Legendenstriches
$graph->legend->SetLineWeight(2);
// Länge des Legendenstriches
$graph->legend->SetMarkAbsHSize(10);
// Postion der Legende einstellen
$graph->legend->SetAbsPos(0, 700);

// Graph "zeichnen" (Bild)
$graph->Stroke();
?>
