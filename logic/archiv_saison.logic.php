<?php

db::initialize_archiv();

$saison = $_GET['saison'];
$saisondetails = Archiv::get_saisondetails($saison);
$turniere = Archiv::get_turniere($saison);
$meisterschafts_tabelle = Tabelle::get_meisterschafts_tabelle(0, $saison, FALSE);

// Da die Rangtabelle erst mit der Saison 2016 (ID 21) eingeführt wurde, wird diese vorher nicht ausgegeben
if ($saison >= 21) {
    $rang_tabelle = Tabelle::get_rang_tabelle(0, $saison, FALSE);
} else {
    $rang_tabelle;
}