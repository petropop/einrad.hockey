<?php

class Archiv
{
    /**
     * Übertragen der Teams in die Archivdatenbank.
     * @param $saison
     */
    
    public static function transfer_teams($saison)
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
}