<?php

class Abstimmung {

    public $beginn_der_abstimmung = "2021-01-17 10:00:00";
    public $ende_der_abstimmung = "2021-01-31 18:00:00";

    // Stimmt ein Team ab, wird die Stimme getrennt von der team_id gespeichert
    static function add_stimme($value) {
        $sql = '
        INSERT INTO `abstimmung_ergebnisse` (`id`, `value`) 
        VALUES (NULL, "' . $value . '")
        ';

        db::writedb($sql);

        return true;
    }

    // Stimmt ein Team ab, wird das Team getrennt von der Stimme gespeichert
    static function add_team($team_id) {
        $sql = '
        INSERT INTO `abstimmung_teams` (`team_id`, `value`)
        VALUES (' . $team_id . ', 1)
        ';

        db::writedb($sql);

        return true;
    }

    // Überprüfung, ob ein Team bereits abgestimmt hat
    function get_team($team_id) {
        $sql = '
        SELECT * 
        FROM abstimmung_teams 
        WHERE team_id = '. $team_id . '
        ';

        $result = db::readdb($sql);
        $data = mysqli_fetch_assoc($result);
        
        // Check, ob Team bereits abgestimmt hat
        // true -> Das Team darf noch abstimmen; false -> Das Team hat bereits abgestimmt
        if (empty($data)) {
            return true;
        } else {
            return false;
        }
    }
}