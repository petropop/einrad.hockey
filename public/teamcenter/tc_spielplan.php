<!-- 
TODO:
- Datum und Ort des Turniers in den "Spielplan" einfügen
- Title entsprechend den anderen anpassen
-->
<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../logic/first.logic.php'; //autoloader und Session
require_once '../../logic/session_team.logic.php'; //Auth

$turnier_id = 915;
//$turnier_id=$_GET['turnier_id'];
$akt_turnier=new Turnier($turnier_id);
//Existiert das Turneir??
if(empty($akt_turnier->daten)){
    Form::error("Turnier wurde nicht gefunden");
    header('Location: ../teamcenter/tc_start.php');
    die();
}
//Besteht die Berechtigung das Turnier zu bearbeiten? 
//if ($_SESSION['team_id'] != $akt_turnier->daten['ausrichter']){
//    Form::error("Keine Berechtigung das Turnier zu bearbeiten");
//    header('Location: ../teamcenter/tc_start.php');
//    die();
//}

$spielplan = new Spielplan($turnier_id);
$spielplan->create_spielplan_jgj();
//einegtragene Tore speichern falls vorher eingetragen
if(isset($_POST["gesendet_tur"])){
    for($i=0;$i<$spielplan->get_anzahl_spiele();$i++){
        echo "in tc_spielplan -> reloaden Spiel:".$i." <br>";
        $spielplan->update_spiel($i+1,$_POST["toreAPOST"][$i],$_POST["toreBPOST"][$i],$_POST["penAPOST"][$i],$_POST["penBPOST"][$i]);
    }
}
$tabelle=$spielplan->get_turnier_tabelle();
$teamliste=$spielplan->teamliste;
$spielliste=$spielplan->get_spiele();
$penalty_warning=$spielplan->penalty_warning;
if(empty($penalty_warning)){
    $penalty_warning=" Kein Penalty notwendig";
}else{
    Form::attention($penalty_warning);
}
//Turnierergebnisse speichern
//TODO nach Datum testen, ist es später ale Turnier begin und nicht merh als X Tage nach Turneir??
if(isset($_POST["gesendet_turnierergebnisse"])){
    //Sind alle spiele gespielt und kein Penalty mehr notwendig
    $spielplan->set_ergebnis($tabelle);
}
$ort=$spielplan->ort;
$datum=$spielplan->datum;
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
$titel = "Spielplan Teamcenter";
$content = "Der Spielplan für das Einradhockey-Turnier in ". $akt_turnier->daten['ort'] . "am" . date("d.m.Y", strtotime($akt_turnier->daten['datum']));
include '../../templates/header.tmp.php';
?>

<!-- TEAMLISTE -->
<?php
include '../../templates/spielplan_vorTurnierTabelle.tmp.php';
?>
<!-- SPIELE -->
<form method="post">
<h3 class="w3-text-secondary w3-margin-top">Spiele</h3>
<form method="post">
    <?php
        include '../../templates/spielplan_spieleTabelleForm.tmp.php';
    ?>
     <p><input type="submit" name="gesendet_tur" class="w3-block w3-button w3-tertiary" value="Spiele senden"></p>
</form>

<!-- Penalty Warnung -->
<h3 class="w3-text-secondary w3-margin-top">Penalty</h3>
<p> <?= $penalty_warning?></p>

<!-- ABSCHLUSSTABELLE -->
<?php
    include '../../templates/spielplan_ergebnisTabelle.tmp.php';
?>
<form method="post">
    <p>
        <input type="submit" name="gesendet_turnierergebnisse" class="w3-block w3-button w3-tertiary" value="Ergebnisse speichern">
    </p>
</form>
<?php
include '../../templates/footer.tmp.php';