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
            SELECT team_id, ? as saison, teamname, ligateam
            FROM `teams_liga`
            WHERE aktiv = 'Ja'
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        db::terminate();
        
        db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');

        $insert_sql = "
            INSERT INTO archiv_teams_liga (team_id, saison, teamname, ligateam)
            VALUES (?, ?, ?, ?)
        ";        

        foreach ($result as $team) {
            db::$db->query($insert_sql, $team['team_id'], $team['saison'], $team['teamname'], $team['ligateam']);
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
            SELECT turniere_liga.turnier_id, saison, spieltag, datum, plaetze, tblock, art
            FROM turniere_liga
            LEFT JOIN turniere_details ON turniere_liga.turnier_id = turniere_details.turnier_id
            WHERE saison = ?
            AND phase = 'ergebnis'
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        db::terminate();
        
        db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');

        $insert_sql = "
            INSERT INTO archiv_turniere_liga (turnier_id, saison, spieltag, datum, plaetze, tblock, art) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";        

        foreach ($result as $turnier) {
            db::$db->query($insert_sql, $turnier['turnier_id'], $turnier['saison'], $turnier['spieltag'], $turnier['datum'], $turnier['plaetze'], $turnier['tblock'], $turnier['art']);
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
            SELECT spiele.*
            FROM spiele
            LEFT JOIN turniere_liga ON spiele.turnier_id = turniere_liga.turnier_id
            WHERE turniere_liga.saison = ?
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        db::terminate();
        
        db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');

        $insert_sql = "
            INSERT INTO archiv_turniere_spiele (turnier_id, spiel_id, team_id_a, team_id_b, schiri_team_id_a, schiri_team_id_b, tore_a, tore_b, penalty_a, penalty_b)
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
            SELECT turniere_ergebnisse.team_id, turniere_ergebnisse.turnier_id, ergebnis, platz
            FROM turniere_ergebnisse
            LEFT JOIN turniere_liga ON turniere_ergebnisse.turnier_id = turniere_liga.turnier_id
            WHERE turniere_liga.saison = ?
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        db::terminate();
        
        db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');

        $insert_sql = "
            INSERT INTO archiv_turniere_ergebnisse (team_id, turnier_id, ergebnis, platz)
            VALUES (?, ?, ?, ?)
        ";        

        foreach ($result as $ergebnis) {
            db::$db->query($insert_sql, $ergebnis['team_id'], $ergebnis['turnier_id'], $ergebnis['ergebnis'], $ergebnis['platz']);
        }

        db::terminate();
        db::initialize();
    }

    public static function transfer_turnierdetails(int $saison)
    {
        $extract_sql = "
            SELECT turniere_liga.turnier_id, tname, hallenname, startzeit, ausrichter, ort, format
            FROM turniere_liga
            LEFT JOIN turniere_details ON turniere_liga.turnier_id = turniere_details.turnier_id
            WHERE turniere_liga.phase = 'ergebnis'
            AND saison = ?
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        db::terminate();
        
        db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');

        $insert_sql = "
            INSERT INTO archiv_turniere_details (turnier_id, tname, hallenname, startzeit, ausrichter, ort, format)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";        

        foreach ($result as $detail) {
            db::$db->query($insert_sql, $detail['turnier_id'], $detail['tname'], $detail['hallenname'], $detail['startzeit'], $detail['ausrichter'], $detail['ort'], $detail['format']);
        }

        db::terminate();
        db::initialize();
    }

    public static function transfer_teamstrafen(int $saison)
    {
        $extract_sql = "
            SELECT team_id, turnier_id, prozentsatz
            FROM teams_strafen
            WHERE saison = ?
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        db::terminate();

        db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');

        $insert_sql = "
            INSERT INTO archiv_teams_strafen (team_id, turnier_id, strafe)
            VALUES (?, ?, ?)
        ";

        foreach ($result as $strafe) {
            db::$db->query($insert_sql, $strafe['team_id'], $strafe['turnier_id'], $strafe['strafe']);
        }

        db::terminate();
        db::initialize();
    }

    public static function transfer_spielplandetails(int $saison)
    {
        $extract_sql = "
            SELECT spielplan, plaetze, anzahl_halbzeiten, halbzeit_laenge, puffer, pausen
            FROM spielplan_details
            LEFT JOIN turniere_liga ON turniere_liga.spielplan_vorlage = spielplan_details.spielplan
            WHERE turniere_liga.saison = ?
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        db::terminate();

        db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');

        $insert_sql = "
            INSERT INTO archiv_spielplan_details (spielplan, plaetze, anzahl_halbzeiten, halbzeit_laenge, puffer, pausen)
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        foreach ($result as $spielplan) {
            db::$db->query($insert_sql, $spielplan['spielplan'], $spielplan['plaetze'], $spielplan['anzahl_halbzeiten'], $spielplan['halbzeit_laenge'], $spielplan['puffer'], $spielplan['pausen']);
        }

        db::terminate();
        db::initialize();
    }

    public static function get_uebersicht()
    {
        db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');
        
        $sql = "
            SELECT saisonname, archiv_turniere_liga.saison, turniere.anzahl as turnier_anzahl, teams.anzahl as teams_anzahl, teamname as meister
            FROM archiv_turniere_liga
            LEFT JOIN archiv_saisons ON archiv_saisons.saison_id = archiv_turniere_liga.saison
            LEFT JOIN (SELECT saison, count(*) as anzahl FROM `archiv_turniere_liga` GROUP BY saison) AS turniere ON archiv_turniere_liga.saison = turniere.saison
            LEFT JOIN (SELECT saison, count(*) as anzahl FROM `archiv_teams_liga` WHERE ligateam = 'Ja' GROUP BY saison) AS teams ON archiv_turniere_liga.saison = teams.saison
            LEFT JOIN (
                SELECT archiv_turniere_liga.saison, archiv_teams_liga.teamname
                FROM archiv_turniere_ergebnisse, archiv_turniere_liga, archiv_teams_liga
                WHERE archiv_turniere_ergebnisse.turnier_id = archiv_turniere_liga.turnier_id
                AND archiv_turniere_ergebnisse.team_id = archiv_teams_liga.team_id
                AND archiv_turniere_ergebnisse.platz = 1
                AND archiv_turniere_ergebnisse.turnier_id = 827) as meister ON archiv_turniere_liga.saison = meister.saison
            GROUP BY archiv_turniere_liga.saison
            ORDER BY archiv_turniere_liga.saison DESC
        ";

        $result = db::$db->query($sql)->esc()->fetch();

        return $result;
    }

    public static function get_turniere(int $saison)
    {
        $sql = "
            SELECT archiv_turniere_liga.turnier_id, datum, ort, art, tblock
            FROM archiv_turniere_liga
            LEFT JOIN archiv_turniere_details ON archiv_turniere_details.turnier_id = archiv_turniere_liga.turnier_id
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
            FROM archiv_turniere_spiele
            LEFT JOIN archiv_turniere_liga ON archiv_turniere_liga.turnier_id = archiv_turniere_spiele.turnier_id
            LEFT JOIN archiv_teams_liga AS teams_a ON archiv_turniere_spiele.team_id_a = teams_a.team_id AND archiv_turniere_liga.saison = teams_a.saison
            LEFT JOIN archiv_teams_liga AS teams_b ON archiv_turniere_spiele.team_id_b = teams_b.team_id AND archiv_turniere_liga.saison = teams_b.saison
            WHERE archiv_turniere_spiele.turnier_id = ?
            ORDER BY spiel_id ASC
        ";

        $result = db::$db->query($sql, $turnier_id)->esc()->fetch();

        return $result;
    }

    public static function get_ergebnisse(int $turnier_id)
    {
        $sql = "
            SELECT platz, teamname, ergebnis
            FROM archiv_turniere_ergebnisse
            LEFT JOIN archiv_turniere_liga ON archiv_turniere_ergebnisse.turnier_id = archiv_turniere_liga.turnier_id
            LEFT JOIN archiv_teams_liga ON archiv_turniere_ergebnisse.team_id = archiv_teams_liga.team_id AND archiv_turniere_liga.saison = archiv_teams_liga.saison
            WHERE archiv_turniere_ergebnisse.turnier_id = ?
            ORDER BY platz ASC
        ";

        $result = db::$db->query($sql, $turnier_id)->esc()->fetch();

        return $result;
    }

    public static function get_teams(int $turnier_id)
    {
        $sql = "
            SELECT teamname
            FROM archiv_turniere_ergebnisse
            LEFT JOIN archiv_turniere_liga ON archiv_turniere_ergebnisse.turnier_id = archiv_turniere_liga.turnier_id
            LEFT JOIN archiv_teams_liga ON archiv_turniere_ergebnisse.team_id = archiv_teams_liga.team_id AND archiv_turniere_liga.saison = archiv_teams_liga.saison
            WHERE archiv_turniere_ergebnisse.turnier_id = ?
            ORDER BY teamname ASC
        ";

        $result = db::$db->query($sql, $turnier_id)->esc()->fetch();

        return $result;
    }

    public static function get_turnierdetails(int $turnier_id)
    {
        $sql = "
            SELECT ort, datum
            FROM archiv_turniere_liga
            LEFT JOIN archiv_turniere_details ON archiv_turniere_liga.turnier_id = archiv_turniere_details.turnier_id
            WHERE archiv_turniere_liga.turnier_id = ?
        ";

        $result = db::$db->query($sql, $turnier_id)->esc()->fetch_row();

        return $result;
    }

    public static function get_saisondetails(int $saison)
    {
        $sql = "
            SELECT saisonname 
            FROM archiv_saisons
            WHERE saison_id = ?
        ";

        $result = db::$db->query($sql, $saison)->esc()->fetch_one();

        return $result;
    }
}