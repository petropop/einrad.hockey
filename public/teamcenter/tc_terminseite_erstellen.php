<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../logic/first.logic.php'; //autoloader und Session
$no_redirect = true;
require_once '../../logic/session_team.logic.php'; //Auth

$liga_team_id = $_SESSION['team_id'];

$akt_team = new Team ($_SESSION['team_id']);
$akt_team_kontakte = new Kontakt ($_SESSION['team_id']);

//Werden an terminseite_erstellen.tmp.php übergeben
$emails = $akt_team_kontakte->get_all_emails();
$daten = $akt_team ->daten();

$change = false; // Wenn sich in teamdaten_aendern.logic etwas ändert, wird $change auf true gesetzt
require_once '../../logic/terminseite_erstellen.logic.php';

/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
include '../../templates/header.tmp.php';
include '../../templates/terminseite_erstellen.tmp.php';
include '../../templates/footer.tmp.php';
