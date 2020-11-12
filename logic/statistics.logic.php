<?php
    
    $statistics = new Statistics();
    $saison = $statistics->saison;
    $turniere = number_format($statistics->turniere, 0, ",", ".");
    $spiele = number_format($statistics->spiele, 0, ",", ".");
    $punkte = number_format($statistics->punkte, 0, ",", ".");
    $gesamt_tore = number_format($statistics->gesamt_tore, 0, ",", ".");
    $spielzeit = secondsToTime($statistics->spielzeit * 60);
    $penalty = number_format($statistics->penalty, 0, ",", ".");
    $tore = number_format($statistics->tore, 0, ",", ".");
    $gegentore = number_format($statistics->gegentore, 0, ",", ".");
    $hoechster_sieg_team_a = $statistics->hoechster_sieg["team_a"];
    $hoechster_sieg_team_b = $statistics->hoechster_sieg["team_b"];
    $hoechster_sieg_tore_a = number_format($statistics->hoechster_sieg["tore_a"], 0, ",", ".");
    $hoechster_sieg_tore_b = number_format($statistics->hoechster_sieg["tore_b"], 0, ",", ".");

    function secondsToTime($seconds) {
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        
        return $dtF->diff($dtT)->format('%ad %hh %imin');
    }

?>