<?php

class Archiv
{
    /**
     * Übertragen der Teams in die Archivdatenbank.
     * @param $saison
     */
    public static function transfer_teams(int $saison)
    {
        $extract_sql = "
            SELECT team_id, teamname, ligateam, ? as saison
            FROM `teams_liga`
            WHERE aktiv = 'Ja'
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        db::terminate();
        
        db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');

        $insert_sql = "
            INSERT INTO teams (team_id, teamname, ligateam, saison)
            VALUES (?, ?, ?, ?)
        ";        

        foreach ($result as $team) {
            db::$db->query($insert_sql, $team['team_id'], $team['teamname'], $team['ligateam'], $team['saison']);
        }

        db::terminate();
        db::initialize();
    }

    /**
     * Übertragen der Turniere in die Archivdatenbank.
     * @param $saison
     */
    public static function transfer_turniere(int $saison) 
    {
        $extract_sql = "
            SELECT turnier_id, tname, art, ausrichter, tblock, datum, saison 
            FROM `turniere_liga` 
            WHERE saison = ? 
            AND turniere_liga.phase = 'ergebnis'
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        db::terminate();
        
        db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');

        $insert_sql = "
            INSERT INTO turniere_liga (turnier_id, tname, art, ausrichter, tblock, datum, saison)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";        

        foreach ($result as $turnier) {
            db::$db->query($insert_sql, $turnier['turnier_id'], $turnier['tname'], $turnier['art'], $turnier['ausrichter'], $turnier['tblock'], $turnier['datum'], $turnier['saison']);
        }

        db::terminate();
        db::initialize();

    }

    /**
     * Übertragen der Spiele in die Archivdatenbank.
     * @param $saison
     */
    public static function transfer_spiele(int $saison)
    {
        $extract_sql = "
            SELECT spiele.*, turniere_liga.saison
            FROM spiele, turniere_liga
            WHERE spiele.turnier_id = turniere_liga.turnier_id
            AND turniere_liga.saison = ?
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        db::terminate();
        
        db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');

        $insert_sql = "
            INSERT INTO spiele (turnier_id, spiel_id, team_id_a, team_id_b, schiri_team_id_a, schiri_team_id_b, tore_a, tore_b, penalty_a, penalty_b)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";        

        foreach ($result as $spiel) {
            db::$db->query($insert_sql, $spiel['turnier_id'], $spiel['spiel_id'], $spiel['team_id_a'], $spiel['team_id_b'], $spiel['schiri_team_id_a'], $spiel['schiri_team_id_b'], $spiel['tore_a'], $spiel['tore_b'], $spiel['penalty_a'], $spiel['penalty_b']);
        }

        db::terminate();
        db::initialize();

    }

    public static function transfer_ergebnisse(int $saison)
    {
        $extract_sql = "
            SELECT turniere_ergebnisse.*
            FROM turniere_ergebnisse, turniere_liga
            WHERE turniere_ergebnisse.turnier_id = turniere_liga.turnier_id
            AND turniere_liga.saison = ?
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        db::terminate();
        
        db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');

        $insert_sql = "
            INSERT INTO turniere_ergebnisse (turnier_ergebnis_id, team_id, turnier_id, ergebnis, platz)
            VALUES (?, ?, ?, ?, ?)
        ";        

        foreach ($result as $ergebnis) {
            db::$db->query($insert_sql, $ergebnis['turnier_ergebnis_id'], $ergebnis['team_id'], $ergebnis['turnier_id'], $ergebnis['ergebnis'], $ergebnis['platz']);
        }

        db::terminate();
        db::initialize();
    }

    public static function transfer_turnierdetails(int $saison)
    {
        $extract_sql = "
            SELECT turniere_details.turnier_id, ort
            FROM turniere_liga
            LEFT JOIN turniere_details ON turniere_details.turnier_id = turniere_liga.turnier_id
            WHERE turniere_liga.phase = 'ergebnis'
            AND saison = ?
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        db::terminate();
        
        db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');

        $insert_sql = "
            INSERT INTO turniere_details (turnier_id, ort)
            VALUES (?, ?)
        ";        

        foreach ($result as $detail) {
            db::$db->query($insert_sql, $detail['turnier_id'], $detail['ort']);
        }

        db::terminate();
        db::initialize();
    }

    public static function get_uebersicht()
    {
        db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');
        
        $sql = "
            SELECT turniere_liga.saison, turniere.anzahl as turnier_anzahl, teams.anzahl as teams_anzahl, teamname as meister
            FROM turniere_liga
            LEFT JOIN (SELECT saison, count(*) as anzahl FROM `turniere_liga` GROUP BY saison) AS turniere ON turniere_liga.saison = turniere.saison
            LEFT JOIN (SELECT saison, count(*) as anzahl FROM `teams` WHERE ligateam = 'Ja' GROUP BY saison) AS teams ON turniere_liga.saison = teams.saison
            LEFT JOIN (
                SELECT turniere_liga.saison, teams.teamname
                FROM turniere_ergebnisse, turniere_liga, teams
                WHERE turniere_ergebnisse.turnier_id = turniere_liga.turnier_id
                AND turniere_ergebnisse.team_id = teams.team_id
                AND turniere_ergebnisse.platz = 1
                AND turniere_ergebnisse.turnier_id = 827) as meister ON turniere_liga.saison = meister.saison
            GROUP BY turniere_liga.saison
            ORDER BY turniere_liga.saison DESC
        ";

        $result = db::$db->query($sql)->esc()->fetch();

        return $result;
    }

    public static function get_turniere(int $saison)
    {
        $sql = "
            SELECT turniere_liga.turnier_id, art, tblock, datum, ort 
            FROM turniere_liga
            LEFT JOIN turniere_details ON turniere_details.turnier_id = turniere_liga.turnier_id
            WHERE saison = ?
            ORDER BY datum ASC
        ";

        $result = db::$db->query($sql, $saison)->esc()->fetch();

        return $result;
    }

    public static function get_spiele(int $turnier_id)
    {
        $sql = "
            SELECT spiel_id, teams_a.teamname AS team_a, teams_b.teamname AS team_b, tore_a, tore_b, penalty_a, penalty_b
            FROM spiele
            LEFT JOIN turniere_liga ON turniere_liga.turnier_id = spiele.turnier_id
            LEFT JOIN teams AS teams_a ON spiele.team_id_a = teams_a.team_id AND turniere_liga.saison = teams_a.saison
            LEFT JOIN teams AS teams_b ON spiele.team_id_b = teams_b.team_id AND turniere_liga.saison = teams_b.saison
            WHERE spiele.turnier_id = ?
            ORDER BY spiel_id ASC
        ";

        $result = db::$db->query($sql, $turnier_id)->esc()->fetch();

        return $result;
    }

    public static function get_ergebnisse(int $turnier_id)
    {
        $sql = "
            SELECT platz, teamname, ergebnis
            FROM turniere_ergebnisse
            LEFT JOIN turniere_liga ON turniere_ergebnisse.turnier_id = turniere_liga.turnier_id
            LEFT JOIN teams ON turniere_ergebnisse.team_id = teams.team_id AND turniere_liga.saison = teams.saison
            WHERE turniere_ergebnisse.turnier_id = ?
            ORDER BY platz ASC
        ";

        $result = db::$db->query($sql, $turnier_id)->esc()->fetch();

        return $result;
    }

    public static function get_teams(int $turnier_id)
    {
        $sql = "
            SELECT teamname
            FROM turniere_ergebnisse
            LEFT JOIN turniere_liga ON turniere_ergebnisse.turnier_id = turniere_liga.turnier_id
            LEFT JOIN teams ON turniere_ergebnisse.team_id = teams.team_id AND turniere_liga.saison = teams.saison
            WHERE turniere_ergebnisse.turnier_id = ?
            ORDER BY teamname ASC
        ";

        $result = db::$db->query($sql, $turnier_id)->esc()->fetch();

        return $result;
    }
}