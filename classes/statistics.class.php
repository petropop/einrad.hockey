<?php

class Statistics{
    
    public $saison = Config::SAISON;
    public $turniere;
    public $spiele;
    public $punkte;
    public $gesamt_tore;
    public $spielzeit;
    public $penalty;
    public $tore;
    public $gegentore;
    public $spielerinnen;
    public $spieler;
    public $kader;
    public $schiedsrichter;
    public $hoechster_sieg;
    public $spiel_wenigste_tore;
    public $spiel_meiste_tore;
    public $torreichstes_unentschieden;
    public $toraermstes_unentschieden;
    
    function __construct() {
        $this->turniere = $this->get_aktuelle_turniere();
        $this->spiele = $this->get_aktuelle_spiele();
        $this->punkte = $this->get_aktuelle_punkte();
        $this->gesamt_tore = $this->get_aktuelle_gesamt_tore();
        $this->spielzeit = $this->get_aktuelle_spielzeit();
        $this->penalty = $this->get_aktuelle_penalty();
        $this->tore = $this->get_aktuelle_tore();
        $this->gegentore = $this->get_aktuelle_gegentore();
        $this->spielerinnen = $this->get_aktuelle_spielerinnen();
        $this->spieler = $this->get_aktuelle_spieler();
        $this->kader = $this->get_aktuelle_kader();
        $this->schiedsrichter = $this->get_aktuelle_schiedsrichter();
        $this->hoechster_sieg=$this->get_hoechster_sieg();
        $this->spiel_wenigste_tore=$this->get_spiel_wenigste_tore();
        $this->spiel_meiste_tore=$this->get_spiel_meiste_tore();
        $this->torreichstes_unentschieden=$this->get_torreichstes_unentschieden();
        $this->toraermstes_unentschieden=$this->get_toraermstes_unentschieden();
    }

    function get_aktuelle_turniere() {
        $sql = "
        SELECT COUNT(*) AS turniere 
        FROM `turniere_liga` 
        WHERE saison = ". $this->saison . "
        AND phase = 'ergebnis'
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result['turniere'];
    }

    function get_aktuelle_spiele() {
        $sql = "
        SELECT COUNT(*) AS spiele 
        FROM `spiele` sp, `turniere_liga` tur 
        WHERE sp.turnier_id = tur.turnier_id 
        AND tur.saison = " . $this->saison . "
        AND tur.phase = 'ergebnis'
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result['spiele'];
    }

    function get_aktuelle_punkte() {
        $sql = "
        SELECT SUM(ergebnis) AS punkte 
        FROM `turniere_ergebnisse` te, `turniere_liga` tl 
        WHERE te.turnier_id = tl.turnier_id 
        AND tl.saison = " . $this->saison . "
        AND tl.phase = 'ergebnis'
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result['punkte'];
    }

    function get_aktuelle_gesamt_tore() {
        $sql = "
        SELECT (SUM(tore_a) + SUM(tore_b)) AS tore 
        FROM `spiele` sp, turniere_liga tur 
        WHERE sp.turnier_id = tur.turnier_id 
        AND tur.saison = 26
        AND tur.phase = 'ergebnis'
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result['tore'];
    }

    function get_aktuelle_spielzeit() {
        $sql = "
        SELECT SUM(sp.spiele * sd.anzahl_halbzeiten * sd.halbzeit_laenge) AS spielzeit 
        FROM 
	        (
	        SELECT tl.turnier_id, COUNT(tl.turnier_id) as plaetze 
	        FROM turniere_liga tl, turniere_details td, turniere_ergebnisse te 
	        WHERE tl.turnier_id = te.turnier_id 
	        AND tl.turnier_id = td.turnier_id 
	        AND tl.saison = " . $this->saison . " 
	        GROUP BY tl.turnier_id
	        ) AS tur,
	        (
	        SELECT turnier_id, COUNT(*) AS spiele 
	        FROM `spiele` 
	        GROUP BY turnier_id
	        ) AS sp,
	        spielplan_details sd
        WHERE tur.turnier_id = sp.turnier_id 
        AND tur.plaetze = sd.plaetze
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result['spielzeit'];
    }

    function get_aktuelle_penalty() {
        $sql = "
        SELECT COUNT(*) AS penalty
        FROM `spiele` sp, `turniere_liga` tur 
        WHERE sp.turnier_id = tur.turnier_id 
        AND tur.saison = " . $this->saison . "
        AND tur.phase = 'ergebnis'
        AND penalty_a IS NOT NULL
        AND penalty_b IS NOT NULL
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result['penalty'];
    }

    function get_aktuelle_tore () {
        $sql = "
        SELECT SUM(sp.tore_a) AS tore 
        FROM `teams_liga` te, `turniere_liga` tur, (SELECT `turnier_id`, `team_id_a`, `team_id_b`, `tore_a`, `tore_b` FROM `spiele` UNION SELECT `turnier_id`, `team_id_b`, `team_id_a`, `tore_b`, `tore_a` FROM `spiele`) AS sp 
        WHERE sp.turnier_id = tur.turnier_id AND sp.team_id_a = te.team_id 
        AND tur.saison = " . $this->saison . " 
        AND tur.phase = 'ergebnis' 
        AND te.ligateam = 'Ja' 
        GROUP BY sp.team_id_a 
        ORDER BY 1 DESC 
        LIMIT 1
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result['tore'];
    }

    function get_aktuelle_gegentore () {
        $sql = "
        SELECT SUM(sp.tore_b) AS gegentore
        FROM `teams_liga` te, `turniere_liga` tur, (SELECT `turnier_id`, `team_id_a`, `team_id_b`, `tore_a`, `tore_b` FROM `spiele` UNION SELECT `turnier_id`, `team_id_b`, `team_id_a`, `tore_b`, `tore_a` FROM `spiele`) AS sp
        WHERE sp.turnier_id = tur.turnier_id
        AND sp.team_id_a = te.team_id
        AND tur.saison = 26
        AND tur.phase = 'ergebnis'
        AND te.ligateam = 'Ja'
        GROUP BY sp.team_id_a
        ORDER BY 1 DESC
        LIMIT 1
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result['gegentore'];
    }

    function get_aktuelle_spielerinnen() {
        $sql = "
        SELECT COUNT(*) AS spielerinnen 
        FROM `spieler` 
        WHERE letzte_saison = " . $this->saison . " 
        AND geschlecht = 'w' 
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);
        return $result['spielerinnen'];
    }


    function get_aktuelle_spieler() {
        $sql = "
        SELECT COUNT(*) AS spieler 
        FROM `spieler` 
        WHERE letzte_saison = " . $this->saison . " 
        AND geschlecht = 'm' 
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);
        return $result['spieler'];
    }

    function get_aktuelle_kader() {
        $sql = "
        SELECT COUNT(*) AS kader
        FROM `spieler`
        WHERE letzte_saison = " . $this->saison . "
        GROUP BY team_id
        ORDER BY 1 DESC
        LIMIT 1
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result['kader'];
    }

    function get_aktuelle_schiedsrichter() {
        $sql = "
        SELECT COUNT(*) AS schiedsrichter
        FROM `spieler` 
        WHERE schiri = 'Ausbilder/in' 
        OR schiri >= " . $this->saison . "
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result['schiedsrichter'];
    }

    function get_hoechster_sieg () {
        $sql = "
        SELECT (SELECT teamname FROM `teams_liga` WHERE team_id = team_id_a) as team_a, (SELECT teamname FROM `teams_liga` WHERE team_id = team_id_b) as team_b, tore_a, tore_b
        FROM `spiele`
        WHERE abs(tore_a-tore_b) = (SELECT 
                              MAX(abs(tore_a-tore_b))
                              FROM `spiele`)
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result;
    }

    function get_spiel_wenigste_tore () {
        $sql = "
        SELECT (SELECT teamname FROM `teams_liga` WHERE team_id = team_id_a) as team_a, (SELECT teamname FROM `teams_liga` WHERE team_id = team_id_b) as team_b, tore_a, tore_b
        FROM `spiele`
        WHERE abs(tore_a+tore_b) = (SELECT 
                              MIN(abs(tore_a+tore_b))
                              FROM `spiele`)
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);
        return $result;
    }

    function get_spiel_meiste_tore () {
        $sql = "
        SELECT (SELECT teamname FROM `teams_liga` WHERE team_id = team_id_a) as team_a, (SELECT teamname FROM `teams_liga` WHERE team_id = team_id_b) as team_b, tore_a, tore_b
        FROM `spiele`
        WHERE abs(tore_a+tore_b) = (SELECT 
                              MAX(abs(tore_a+tore_b))
                              FROM `spiele`)
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);
        return $result;
    }
    function get_torreichstes_unentschieden() {
        $sql = "
        SELECT (SELECT teamname FROM `teams_liga` WHERE team_id = team_id_a) as team_a, (SELECT teamname FROM `teams_liga` WHERE team_id = team_id_b) as team_b, tore_a, tore_b
        FROM `spiele`
        WHERE tore_a=tore_b AND abs(tore_a+tore_b) = (SELECT 
                              MAX(abs(tore_a+tore_b))
                              FROM `spiele` WHERE tore_a=tore_b)
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);
        return $result;
    }
    function get_toraermstes_unentschieden() {
        $sql = "
        SELECT (SELECT teamname FROM `teams_liga` WHERE team_id = team_id_a) as team_a, (SELECT teamname FROM `teams_liga` WHERE team_id = team_id_b) as team_b, tore_a, tore_b
        FROM `spiele`
        WHERE tore_a=tore_b AND abs(tore_a+tore_b) = (SELECT 
                              MIN(abs(tore_a+tore_b))
                              FROM `spiele` WHERE tore_a=tore_b)
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);
        return $result;
    }

}