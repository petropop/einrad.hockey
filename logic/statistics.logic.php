<?php
    if (isset($_GET['saison'])){
        $saison = $_GET['saison'];
    }else{
        $saison = CONFIG::SAISON;
    }
    $statistics = new Statistics($saison);
    $saison = $statistics->saison;
    $turniere = number_format($statistics->turniere, 0, ",", ".");
    $spiele = number_format($statistics->spiele, 0, ",", ".");
    $punkte = number_format($statistics->punkte, 0, ",", ".");
    $gesamt_tore = number_format($statistics->gesamt_tore, 0, ",", ".");
    $spielzeit = secondsToTime($statistics->spielzeit * 60);
    $penalty = number_format($statistics->penalty, 0, ",", ".");
    $tore = array(
        "teamname"=>$statistics->tore["teamname"],
        "tore"=>number_format($statistics->tore["tore"], 0, ",", "."),
    );
    $gegentore = array(
        "teamname"=>$statistics->gegentore["teamname"],
        "gegentore"=>number_format($statistics->gegentore["gegentore"], 0, ",", "."),
    );
    $spielerinnen = number_format($statistics->spielerinnen, 0, ",", ".");
    $spieler = number_format($statistics->spieler, 0, ",", ".");
    $kader = number_format($statistics->kader, 0, ",", ".");
    $schiedsrichter = number_format($statistics->schiedsrichter, 0, ",", ".");
    $hoechster_sieg = array(
        "team_a"=>$statistics->hoechster_sieg["team_a"],
        "team_b"=>$statistics->hoechster_sieg["team_b"],
        "datum"=>$statistics->hoechster_sieg["datum"],
        "tore_a"=>number_format($statistics->hoechster_sieg["tore_a"], 0, ",", "."),
        "tore_b"=>number_format($statistics->hoechster_sieg["tore_b"], 0, ",", "."),
    );
    $spiel_wenigste_tore =  array(
        "team_a"=>$statistics->spiel_wenigste_tore["team_a"],
        "team_b"=>$statistics->spiel_wenigste_tore["team_b"],
        "datum"=>$statistics->spiel_wenigste_tore["datum"],
        "tore_a"=>number_format($statistics->spiel_wenigste_tore["tore_a"], 0, ",", "."),
        "tore_b"=>number_format($statistics->spiel_wenigste_tore["tore_b"], 0, ",", ".")
    );
    $spiel_meiste_tore =  array(
        "team_a"=>$statistics->spiel_meiste_tore["team_a"],
        "team_b"=>$statistics->spiel_meiste_tore["team_b"],
        "datum"=>$statistics->spiel_meiste_tore["datum"],
        "tore_a"=>number_format($statistics->spiel_meiste_tore["tore_a"], 0, ",", "."),
        "tore_b"=>number_format($statistics->spiel_meiste_tore["tore_b"], 0, ",", ".")
    );
    $torreichstes_unentschieden =  array(
        "team_a"=>$statistics->torreichstes_unentschieden["team_a"],
        "team_b"=>$statistics->torreichstes_unentschieden["team_b"],
        "datum"=>$statistics->torreichstes_unentschieden["datum"],
        "tore_a"=>number_format($statistics->torreichstes_unentschieden["tore_a"], 0, ",", "."),
        "tore_b"=>number_format($statistics->torreichstes_unentschieden["tore_b"], 0, ",", ".")
    );
    $toraermstes_unentschieden =  array(
        "team_a"=>$statistics->toraermstes_unentschieden["team_a"],
        "team_b"=>$statistics->toraermstes_unentschieden["team_b"],
        "datum"=>$statistics->toraermstes_unentschieden["datum"],
        "tore_a"=>number_format($statistics->toraermstes_unentschieden["tore_a"], 0, ",", "."),
        "tore_b"=>number_format($statistics->toraermstes_unentschieden["tore_b"], 0, ",", ".")
    );
    $haeufigstes_ergebnis =  array(
        "tore_a"=>$statistics->haeufigstes_ergebnis["tore_a"],
        "tore_b"=>$statistics->haeufigstes_ergebnis["tore_b"],
        "anzahl"=>$statistics->haeufigstes_ergebnis["anzahl"]
    );
    $seriensieger =  array(
        "team_name"=>$statistics->seriensieger["team_name"],
        "max_siege"=>$statistics->seriensieger["max_siege"],
        "datum"=>$statistics->seriensieger["datum"]
    );
    $seriensieger_turnier =  array(
        "team_name"=>$statistics->seriensieger_turnier["team_name"],
        "max_siege"=>$statistics->seriensieger_turnier["max_siege"],
        "datum"=>$statistics->seriensieger_turnier["datum"]
    );
    $turniersiege =  array(
        "team_name"=>$statistics->turniersiege["team_name"],
        "siege"=>$statistics->turniersiege["siege"]
    );
    $turnierteilnahmen =  array(
        "team_name"=>$statistics->turnierteilnahmen["team_name"],
        "teilnahmen"=>$statistics->turnierteilnahmen["teilnahmen"]
    );
    $max_entf_team =  array(
        "entfernung"=>number_format($statistics->max_entf_team["entfernung"], 1, ",", "."),
        "ort_a"=>$statistics->max_entf_team["ort_a"],
        "ort_b"=>$statistics->max_entf_team["ort_b"],
    );
    $max_anreise =  array(
        "entfernung"=>number_format($statistics->max_anreise["entfernung"], 1, ",", "."),
        "teamname"=>$statistics->max_anreise["teamname"],
        "ort"=>$statistics->max_anreise["ort"],
        "turnier_ort"=>$statistics->max_anreise["turnier_ort"],
    );
    $turnier_max_anreise =  array(
        "sum_entfernung"=>number_format($statistics->turnier_max_anreise["sum_entfernung"], 1, ",", "."),
        "turnier_ort"=>$statistics->turnier_max_anreise["turnier_ort"],
        "datum"=>$statistics->turnier_max_anreise["datum"],
    );
    $turnier_min_anreise =  array(
        "sum_entfernung"=>number_format($statistics->turnier_min_anreise["sum_entfernung"], 1, ",", "."),
        "turnier_ort"=>$statistics->turnier_min_anreise["turnier_ort"],
        "datum"=>$statistics->turnier_min_anreise["datum"],
    );

    function secondsToTime($seconds) {
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        
        return $dtF->diff($dtT)->format('%ad %hh %imin');
    }

?>