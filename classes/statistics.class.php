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
    public $seriensieger;
    public $seriensieger_turnier;
    public $turniersiege;
    public $turnierteilnahmen;
    public $max_entf_team;
    public $max_anreise;
    public $turnier_max_anreise;
    public $turnier_min_anreise;

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
        $this->seriensieger=$this->get_seriensieger();
        $this->seriensieger_turnier=$this->get_seriensieger_turnier();
        $this->turniersiege=$this->get_turniersiege();
        $this->turnierteilnahmen=$this->get_turnierteilnahmen();
        $this->max_entf_team=$this->get_max_entfernung_aktiver_ligateams();
        $this->max_anreise=$this->get_max_anreise();
        $this->turnier_max_anreise=$this->get_turnier_max_anreise();
        $this->turnier_min_anreise=$this->get_turnier_min_anreise();
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
        AND tur.saison = " . $this->saison . "
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
        SELECT SUM(sp.tore_a) AS tore,  te.teamname
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
        AND tur.saison = " . $this->saison . "
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

    //Bei den nachfolgend Statistiken zählt das erste Ergebnis. Dabei zählt zuerst das Datum, dann die spiel_id und schließlich die turnier_id. 
    //Nachfolger müssen das Ergebnis daher immer übertreffen. 
    function get_hoechster_sieg () {
        $sql = "
        SELECT 
        (SELECT teamname FROM `teams_liga` WHERE team_id = sp.team_id_a) as team_a, 
        (SELECT teamname FROM `teams_liga` WHERE team_id = sp.team_id_b) as team_b, 
        tur.datum,
        sp.tore_a, 
        sp.tore_b
        FROM `spiele` sp, `turniere_liga` tur
        WHERE sp.turnier_id=tur.turnier_id 
            AND abs(tore_a-tore_b) = (SELECT 
                              MAX(abs(tore_a-tore_b))
                              FROM `spiele`)
            AND tur.phase = 'ergebnis'
        ORDER BY tur.datum, sp.spiel_id, sp.turnier_id
        LIMIT 1
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result;
    }

    function get_spiel_wenigste_tore () {
        $sql = "
        SELECT (SELECT teamname FROM `teams_liga` WHERE team_id = sp.team_id_a) as team_a, 
        (SELECT teamname FROM `teams_liga` WHERE team_id = sp.team_id_b) as team_b,
        tur.datum,
        sp.tore_a, 
        sp.tore_b
        FROM `spiele` sp, `turniere_liga` tur
        WHERE sp.turnier_id=tur.turnier_id 
            AND abs(tore_a+tore_b) = (SELECT 
                              MIN(abs(tore_a+tore_b))
                              FROM `spiele`)
            AND tur.phase = 'ergebnis'
        ORDER BY tur.datum, sp.spiel_id, sp.turnier_id
        LIMIT 1
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);
        return $result;
    }

    function get_spiel_meiste_tore () {
        $sql = "
        SELECT (SELECT teamname FROM `teams_liga` WHERE team_id = sp.team_id_a) as team_a, 
        (SELECT teamname FROM `teams_liga` WHERE team_id = sp.team_id_b) as team_b,
        tur.datum,
        sp.tore_a, 
        sp.tore_b
        FROM `spiele` sp, `turniere_liga` tur
        WHERE sp.turnier_id=tur.turnier_id 
            AND abs(tore_a+tore_b) = (SELECT 
                              MAX(abs(tore_a+tore_b))
                              FROM `spiele`)
            AND tur.phase = 'ergebnis'
        ORDER BY tur.datum, sp.spiel_id, sp.turnier_id
        LIMIT 1
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);
        return $result;
    }
    function get_torreichstes_unentschieden() {
        $sql = "
        SELECT (SELECT teamname FROM `teams_liga` WHERE team_id = sp.team_id_a) as team_a, 
        (SELECT teamname FROM `teams_liga` WHERE team_id = sp.team_id_b) as team_b,
        tur.datum,
        tur.saison,
        sp.tore_a, 
        sp.tore_b
        FROM `spiele` sp, `turniere_liga` tur
        WHERE sp.turnier_id=tur.turnier_id 
            AND tore_a=tore_b 
            AND abs(tore_a+tore_b) = (SELECT 
                              MAX(abs(tore_a+tore_b))
                              FROM `spiele` WHERE tore_a=tore_b)
            AND tur.phase = 'ergebnis'
        ORDER BY tur.datum, sp.spiel_id, sp.turnier_id
        LIMIT 1
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);
        return $result;
    }
    function get_toraermstes_unentschieden() {
        $sql = "
        SELECT (SELECT teamname FROM `teams_liga` WHERE team_id = sp.team_id_a) as team_a, 
        (SELECT teamname FROM `teams_liga` WHERE team_id = sp.team_id_b) as team_b,
        tur.datum,
        tur.saison,
        sp.tore_a, 
        sp.tore_b
        FROM `spiele` sp, `turniere_liga` tur
        WHERE sp.turnier_id=tur.turnier_id 
            AND tore_a=tore_b 
            AND abs(tore_a+tore_b) = (SELECT 
                              MIN(abs(tore_a+tore_b))
                              FROM `spiele` WHERE tore_a=tore_b)
            AND tur.phase = 'ergebnis'
        ORDER BY tur.datum, sp.spiel_id, sp.turnier_id
        LIMIT 1
        ";
        //
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);
        return $result;
    }
    function get_seriensieger() {
        $sql0 = "
        SELECT `team_id`, `teamname` 
        FROM `teams_liga`
        ";
        $team_liste = db::readdb($sql0);
        $team_liste = mysqli_fetch_all($team_liste);
        $max_siege=0;
        $min_datum = "1994-01-01";
        $team_name='';
        foreach ($team_liste as $value)
        {
            //MJC Trier - Die Gladiatoren
            //SET @count=0;
            //SELECT MAX(@count:=((tore_a>tore_b)*(team_id_a=155)+(tore_b>tore_a)*(team_id_b=155))*(@count+((tore_a>tore_b)*(team_id_a=155)+(tore_b>tore_a)*(team_id_b=155)))) 
            //FROM `spiele` sp 
            //WHERE team_id_a=155 OR team_id_b=155
            //ORDER BY (SELECT `datum` from `turniere_liga` tur WHERE tur.turnier_id = sp.turnier_id), `spiel_id`
            
            //Einradfüchse 2 team_id=421

            //Anzahl Seriensiege pro Team
            $sql1 = "SET @count:=0;";
            $sql2 = "SELECT @count:=((tore_a>tore_b)*(team_id_a=$value[0])+(tore_b>tore_a)*(team_id_b=$value[0]))*(@count+((tore_a>tore_b)*(team_id_a=$value[0])+(tore_b>tore_a)*(team_id_b=$value[0])))
            as siege, tur.datum
            FROM `spiele` sp, `turniere_liga` tur 
            WHERE tur.turnier_id=sp.turnier_id
                AND (team_id_a=$value[0] OR team_id_b=$value[0])
            ORDER BY tur.datum, sp.spiel_id
            ";
            db::readdb($sql1);
            $result_per_team = db::readdb($sql2);
            $result_per_team = mysqli_fetch_all($result_per_team);
            $max_siege_team = 0;
            $datum_team = "1994-01-01";
            foreach($result_per_team as $res_team)
            {
                if ($res_team[0]>$max_siege_team)
                    $max_siege_team = $res_team[0];
                    $datum_team = $res_team[1];
            }
            if ($max_siege_team> $max_siege or ($max_siege_team = $max_siege and $datum_team < $min_datum))
            {
                $max_siege = $max_siege_team;
                $datum = $datum_team;
                $team_name = $value[1];
            }
        }
        $result = array(
            "team_name" => $team_name,
            "max_siege" => $max_siege,
            "datum" => $datum
        );
        return $result;
    }
    //seriensieger
    //SELECT * FROM `spiele` sp ORDER BY (SELECT `datum` from `turniere_liga` tur WHERE tur.turnier_id = sp.turnier_id)
    //siegesserie für id=837
    //SELECT (tore_a>tore_b)*(team_id_a=837)+(tore_b>tore_a)*(team_id_b=837) FROM `spiele` sp WHERE team_id_a=837 OR team_id_b=837 ORDER BY (SELECT `datum` from `turniere_liga` tur WHERE tur.turnier_id = sp.turnier_id), `spiel_id`
    function get_seriensieger_turnier() {
        $sql0 = "
        SELECT `team_id`, `teamname` 
        FROM `teams_liga`
        ";
        $team_liste = db::readdb($sql0);
        $team_liste = mysqli_fetch_all($team_liste);
        $max_siege=0;
        $min_datum = "1994-01-01";
        $team_name='';
        foreach ($team_liste as $value)
        {
            //Einradfüchse 2 team_id=421

            //Anzahl Turniersiege in Serie pro Team
            $sql1 = "SET @count:=0;";
            $sql2 = "SELECT (@count:=(tur_erg.platz=1)*(@count+(tur_erg.platz=1)))as siege, datum
            FROM `turniere_ergebnisse` tur_erg, `turniere_liga` tur
            WHERE tur_erg.turnier_id = tur.turnier_id
            AND team_id=$value[0]
            ORDER BY tur.datum
            ";
            db::readdb($sql1);
            $result_per_team = db::readdb($sql2);
            $result_per_team = mysqli_fetch_all($result_per_team);
            $max_siege_team = 0;
            $datum_team = "1994-01-01";
            foreach($result_per_team as $res_team)
            {
                if ($res_team[0]>$max_siege_team)
                    $max_siege_team = $res_team[0];
                    $datum_team = $res_team[1];
            }
            if ($max_siege_team> $max_siege or ($max_siege_team = $max_siege and $datum_team < $min_datum))
            {
                $max_siege = $max_siege_team;
                $datum = $datum_team;
                $team_name = $value[1];
            }
        }
        $result = array(
            "team_name" => $team_name,
            "max_siege" => $max_siege,
            "datum" => $datum
        );
        return $result;
    }

    function get_turniersiege() {
        $sql = "
        SELECT  tl.teamname as team_name, COUNT( tur_erg.team_id) as siege 
        FROM `turniere_ergebnisse` tur_erg,`teams_liga`tl
        WHERE platz=1
        AND tur_erg.team_id = tl.team_id
        GROUP BY  tur_erg.team_id
        ORDER BY siege DESC
        LIMIT 1
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result;
    }

    function get_turnierteilnahmen() {
        $sql = "
        SELECT  tl.teamname as team_name, COUNT( tur_erg.team_id) as teilnahmen
        FROM `turniere_ergebnisse` tur_erg,`teams_liga`tl
        WHERE tur_erg.team_id = tl.team_id
        GROUP BY  tur_erg.team_id
        ORDER BY teilnahmen DESC
        LIMIT 1
        ";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result;
    }

    //entfernungen
    function get_max_entfernung_aktiver_ligateams()
    {
        $sql = "SELECT entf.entfernung,
        (SELECT ort FROM `teams_details` td WHERE td.team_id = entf.team_id_a) as ort_a,
        (SELECT ort FROM `teams_details` td WHERE td.team_id = entf.team_id_b) as ort_b
        FROM `entfernungen` entf 
        WHERE entf.entfernung = (SELECT MAX(entfernung)FROM `entfernungen`)
        AND (SELECT ort FROM `teams_details` td WHERE td.team_id = entf.team_id_a) <
        (SELECT ort FROM `teams_details` td WHERE td.team_id = entf.team_id_b)
        AND (SELECT aktiv FROM `teams_liga`tl WHERE tl.team_id=entf.team_id_a) = 'ja'
        AND (SELECT aktiv FROM `teams_liga`tl WHERE tl.team_id=entf.team_id_b) = 'ja'
        AND (SELECT ligateam FROM `teams_liga`tl WHERE tl.team_id=entf.team_id_a) = 'ja'
        AND (SELECT ligateam FROM `teams_liga`tl WHERE tl.team_id=entf.team_id_b) = 'ja'
        ORDER BY entf.team_id_a, entf.team_id_b
        LIMIT 1";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);
        return $result;
    }
    function get_max_anreise()
    {
        $sql = "SELECT (SELECT tl.teamname FROM `teams_liga`tl WHERE tl.team_id = tur_erg.team_id)as teamname,
        (SELECT td.ort FROM `teams_details`td WHERE td.team_id = tur_erg.team_id)as ort, 
        (SELECT tl.teamname FROM `teams_liga`tl WHERE tl.team_id = tur.ausrichter)as ausrichter_name,
        (SELECT td.ort FROM `teams_details`td WHERE td.team_id = tur.ausrichter)as turnier_ort, 
        entf.entfernung
        FROM `turniere_ergebnisse` tur_erg, `turniere_liga` tur, `entfernungen` entf, `teams_liga` tl_ausrichter
        WHERE tur_erg.turnier_id = tur.turnier_id
        AND entf.team_id_a = tur_erg.team_id
        AND entf.team_id_b = tur.ausrichter
        AND tl_ausrichter.team_id = tur.ausrichter
        ORDER BY entf.entfernung DESC
        LIMIT 1";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);
        return $result;
    }
    function get_turnier_max_anreise()
    {
        $sql = "SELECT tur.datum,
        (SELECT td.ort FROM `teams_details`td WHERE td.team_id = tur.ausrichter)as turnier_ort, 
        SUM(entf.entfernung) as sum_entfernung
        FROM `turniere_ergebnisse` tur_erg, `turniere_liga` tur, `entfernungen` entf, `teams_liga` tl_ausrichter
        WHERE tur_erg.turnier_id = tur.turnier_id
        AND entf.team_id_a = tur_erg.team_id
        AND entf.team_id_b = tur.ausrichter
        AND tl_ausrichter.team_id = tur.ausrichter
        GROUP BY tur.turnier_id
        ORDER BY sum_entfernung DESC
        LIMIT 1";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);
        return $result;
    }
    function get_turnier_min_anreise()
    {
        $sql = "SELECT tur.datum,
        (SELECT td.ort FROM `teams_details`td WHERE td.team_id = tur.ausrichter)as turnier_ort, 
        SUM(entf.entfernung) as sum_entfernung
        FROM `turniere_ergebnisse` tur_erg, `turniere_liga` tur, `entfernungen` entf, `teams_liga` tl_ausrichter
        WHERE tur_erg.turnier_id = tur.turnier_id
        AND entf.team_id_a = tur_erg.team_id
        AND entf.team_id_b = tur.ausrichter
        AND tl_ausrichter.team_id = tur.ausrichter
        GROUP BY tur.turnier_id
        ORDER BY sum_entfernung
        LIMIT 1";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);
        return $result;
    }
}