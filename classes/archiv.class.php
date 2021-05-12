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
}