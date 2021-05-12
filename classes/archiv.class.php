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
    public static function transfer_turniere(int $saison) {
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
}