<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../logic/first.logic.php'; //autoloader und Session
require_once '../../logic/la_session.logic.php'; //Auth
require_once '../../logic/turnier_bearbeiten_first.logic.php'; //Turnier und $daten-Array erstellen + Sanitizing + Berechtigung Prüfen + Existiert das Turnier?
require_once '../../logic/turnier_bearbeiten_la.logic.php'; //Formularauswertung für Ligaausschuss
require_once '../../logic/turnier_bearbeiten_teams.logic.php'; //Formularauswertung für Turnierdetails

/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
include '../../templates/header.tmp.php';
?>

<h2>Turnierdaten ändern (Ligaausschuss):</h2>
<div class="w3-tertiary w3-card-4 w3-panel w3-center">
    <h3><?=$daten['ort']?> (<?=$daten['tblock']?>) am <?=date("d.m.Y", strtotime($daten['datum']))?> </h3>
</div>
<p>
    <a href='../liga/turnier_details.php?turnier_id=<?=$daten['turnier_id']?>'><button class="w3-button w3-text-blue no">Zu den Turnierdetails</button></a>
    <a href='../ligacenter/lc_turnierliste.php?turnier_id=<?=$daten['turnier_id']?>'><button style='display: inline;' class="w3-button w3-right w3-text-blue no">Turniere verwalten (Liste)</button></a>
</p>

<h3 class="w3-text-grey">Folgende Turnierdaten dürfen nur vom Ligaausschuss geändert werden</h3>
<?php include '../../templates/turnier_bearbeiten_la.tmp.php';?>

<h3 class="w3-text-grey">Folgende Turnierdaten können auch vom Ausrichter geändert werden. Als Ligaausschuss hat man allerdings keine Restriktionen.</h3>
<?php 
include '../../templates/turnier_bearbeiten_teams.tmp.php';

include '../../templates/footer.tmp.php';