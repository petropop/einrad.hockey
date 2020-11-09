<?php
    
    $statistics = new Statistics();
    $saison = $statistics->saison;
    $turniere = number_format($statistics->turniere, 0, ",", ".");
    $spiele = number_format($statistics->spiele, 0, ",", ".");
    $punkte = number_format($statistics->punkte, 0, ",", ".");

?>