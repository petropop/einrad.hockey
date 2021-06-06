<?php

db::initialize_archiv();

$turnier_id = $_GET['turnier_id'];
$teams = Archiv::get_teams($turnier_id);
$spiele = Archiv::get_spiele($turnier_id);
$ergebnisse = Archiv::get_ergebnisse($turnier_id);
$turnierdetails = Archiv::get_turnierdetails($turnier_id);
if ($turnierdetails['saison'] >= 21) {
    $tabelle = Tabelle::get_rang_tabelle($turnierdetails['spieltag'], $turnierdetails['saison'], FALSE);
} else {
    $tabelle = Tabelle::get_meisterschafts_tabelle($turnierdetails['spieltag'], $turnierdetails['saison'], FALSE);
}

$nlteams = FALSE;
foreach ($teams as $team) {
    if ($team['ligateam'] == 'Nein') {
        $nlteams = TRUE; 
    }
}