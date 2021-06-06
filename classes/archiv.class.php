<?php

class Archiv
{
    public static function archivieren(int $saison)
    {
        self::transfer_teams($saison);
        self::transfer_turniere($saison);
        self::transfer_spiele($saison);
        self::transfer_ergebnisse($saison);
        self::transfer_turnierdetails($saison);
        self::transfer_teamstrafen($saison);
        self::transfer_spielplandetails($saison);
    }

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

        $insert_sql = "
            INSERT INTO archiv_teams_liga (team_id, saison, teamname, ligateam)
            VALUES (?, ?, ?, ?)
        ";
        
        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        foreach ($result as $team) {
            db::$archiv->query($insert_sql, $team['team_id'], $team['saison'], $team['teamname'], $team['ligateam']);
        }
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

        $insert_sql = "
            INSERT INTO archiv_turniere_liga (turnier_id, saison, spieltag, datum, plaetze, tblock, art) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        foreach ($result as $turnier) {
            db::$archiv->query($insert_sql, $turnier['turnier_id'], $turnier['saison'], $turnier['spieltag'], $turnier['datum'], $turnier['plaetze'], $turnier['tblock'], $turnier['art']);
        }
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

        $insert_sql = "
            INSERT INTO archiv_turniere_spiele (turnier_id, spiel_id, team_id_a, team_id_b, schiri_team_id_a, schiri_team_id_b, tore_a, tore_b, penalty_a, penalty_b)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";  

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();      

        foreach ($result as $spiel) {
            db::$archiv->query($insert_sql, $spiel['turnier_id'], $spiel['spiel_id'], $spiel['team_id_a'], $spiel['team_id_b'], $spiel['schiri_team_id_a'], $spiel['schiri_team_id_b'], $spiel['tore_a'], $spiel['tore_b'], $spiel['penalty_a'], $spiel['penalty_b']);
        }
    }

    public static function transfer_ergebnisse(int $saison)
    {
        $extract_sql = "
            SELECT turniere_ergebnisse.team_id, turniere_ergebnisse.turnier_id, ergebnis, platz
            FROM turniere_ergebnisse
            LEFT JOIN turniere_liga ON turniere_ergebnisse.turnier_id = turniere_liga.turnier_id
            WHERE turniere_liga.saison = ?
        ";

        $insert_sql = "
            INSERT INTO archiv_turniere_ergebnisse (team_id, turnier_id, ergebnis, platz)
            VALUES (?, ?, ?, ?)
        ";  

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();      

        foreach ($result as $ergebnis) {
            db::$archiv->query($insert_sql, $ergebnis['team_id'], $ergebnis['turnier_id'], $ergebnis['ergebnis'], $ergebnis['platz']);
        }
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

        $insert_sql = "
            INSERT INTO archiv_turniere_details (turnier_id, tname, hallenname, startzeit, ausrichter, ort, format)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";        

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        foreach ($result as $detail) {
            db::$archiv->query($insert_sql, $detail['turnier_id'], $detail['tname'], $detail['hallenname'], $detail['startzeit'], $detail['ausrichter'], $detail['ort'], $detail['format']);
        }
    }

    public static function transfer_teamstrafen(int $saison)
    {
        $extract_sql = "
            SELECT team_id, turnier_id, prozentsatz
            FROM teams_strafen
            WHERE saison = ?
        ";

        $insert_sql = "
            INSERT INTO archiv_teams_strafen (team_id, turnier_id, strafe)
            VALUES (?, ?, ?)
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        foreach ($result as $strafe) {
            db::$archiv->query($insert_sql, $strafe['team_id'], $strafe['turnier_id'], $strafe['strafe']);
        }
    }

    public static function transfer_spielplandetails(int $saison)
    {
        $extract_sql = "
            SELECT spielplan, plaetze, anzahl_halbzeiten, halbzeit_laenge, puffer, pausen
            FROM spielplan_details
            LEFT JOIN turniere_liga ON turniere_liga.spielplan_vorlage = spielplan_details.spielplan
            WHERE turniere_liga.saison = ?
        ";

        $insert_sql = "
            INSERT INTO archiv_spielplan_details (spielplan, plaetze, anzahl_halbzeiten, halbzeit_laenge, puffer, pausen)
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $result = db::$db->query($extract_sql, $saison)->esc()->fetch();

        foreach ($result as $spielplan) {
            db::$archiv->query($insert_sql, $spielplan['spielplan'], $spielplan['plaetze'], $spielplan['anzahl_halbzeiten'], $spielplan['halbzeit_laenge'], $spielplan['puffer'], $spielplan['pausen']);
        }
    }

    public static function get_uebersicht()
    {
        $sql = "
            SELECT saisonname, archiv_turniere_liga.saison, turniere.anzahl as turnier_anzahl, teams.anzahl as teams_anzahl, teamname as meister
            FROM archiv_turniere_liga
            LEFT JOIN archiv_saisons ON archiv_saisons.saison_id = archiv_turniere_liga.saison
            LEFT JOIN (SELECT saison, count(*) as anzahl FROM `archiv_turniere_liga` GROUP BY saison) AS turniere ON archiv_turniere_liga.saison = turniere.saison
            LEFT JOIN (SELECT saison, count(*) as anzahl FROM `archiv_teams_liga` WHERE ligateam = 'Ja' GROUP BY saison) AS teams ON archiv_turniere_liga.saison = teams.saison
            LEFT JOIN (
                SELECT archiv_turniere_liga.saison, archiv_teams_liga.teamname
                FROM archiv_turniere_liga
                LEFT JOIN archiv_turniere_ergebnisse ON archiv_turniere_liga.turnier_id = archiv_turniere_ergebnisse.turnier_id
                LEFT JOIN archiv_teams_liga ON archiv_turniere_ergebnisse.team_id = archiv_teams_liga.team_id AND archiv_turniere_liga.saison = archiv_teams_liga.saison
                WHERE tblock = 'FINALE'
                AND platz = 1) as meister ON archiv_turniere_liga.saison = meister.saison
            GROUP BY archiv_turniere_liga.saison
            ORDER BY archiv_turniere_liga.saison DESC
        ";

        $result = db::$archiv->query($sql)->esc()->fetch();

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

        $result = db::$archiv->query($sql, $saison)->esc()->fetch();

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

        $result = db::$archiv->query($sql, $turnier_id)->esc()->fetch();

        return $result;
    }

    public static function get_ergebnisse(int $turnier_id)
    {
        $sql = "
            SELECT platz, teamname, ergebnis, ligateam
            FROM archiv_turniere_ergebnisse
            LEFT JOIN archiv_turniere_liga ON archiv_turniere_ergebnisse.turnier_id = archiv_turniere_liga.turnier_id
            LEFT JOIN archiv_teams_liga ON archiv_turniere_ergebnisse.team_id = archiv_teams_liga.team_id AND archiv_turniere_liga.saison = archiv_teams_liga.saison
            WHERE archiv_turniere_ergebnisse.turnier_id = ?
            ORDER BY platz ASC
        ";

        $result = db::$archiv->query($sql, $turnier_id)->esc()->fetch();

        return $result;
    }

    public static function get_teams(int $turnier_id)
    {
        $sql = "
            SELECT archiv_teams_liga.team_id, teamname, ligateam
            FROM archiv_turniere_ergebnisse
            LEFT JOIN archiv_turniere_liga ON archiv_turniere_ergebnisse.turnier_id = archiv_turniere_liga.turnier_id
            LEFT JOIN archiv_teams_liga ON archiv_turniere_ergebnisse.team_id = archiv_teams_liga.team_id AND archiv_turniere_liga.saison = archiv_teams_liga.saison
            WHERE archiv_turniere_ergebnisse.turnier_id = ?
            ORDER BY teamname ASC
        ";

        $result = db::$archiv->query($sql, $turnier_id)->esc()->fetch();

        return $result;
    }

    public static function get_turnierdetails(int $turnier_id)
    {
        $sql = "
            SELECT ort, datum, spieltag, saison
            FROM archiv_turniere_liga
            LEFT JOIN archiv_turniere_details ON archiv_turniere_liga.turnier_id = archiv_turniere_details.turnier_id
            WHERE archiv_turniere_liga.turnier_id = ?
        ";

        $result = db::$archiv->query($sql, $turnier_id)->esc()->fetch_row();

        return $result;
    }

    public static function get_saisondetails(int $saison)
    {
        $sql = "
            SELECT saisonname 
            FROM archiv_saisons
            WHERE saison_id = ?
        ";

        $result = db::$archiv->query($sql, $saison)->esc()->fetch_one();

        return $result;
    }

        /**
     * Gibt die Platzierung eines Teams in der Rangtabelle zurück
     *
     * @param int $team_id
     * @param int|null $spieltag
     * @return int|null
     */
    public static function get_team_rang(int $team_id, int $spieltag = NULL): ?int
    {
        return $rangtabelle[$spieltag][$team_id]['rang'] ?? NULL;
    }

        /**
     * Weist dem Platz in der Rangtabelle eine Wertung zu
     *
     * @param int|null $rang
     * @return int|null
     */
    public static function rang_to_wertigkeit(?int $rang, int $saison): ?int
    {
        // Nichtligateam
        if (is_null($rang)){
            return NULL;
        }

        if ($saison >= 22) {
            // Platz 1 bis 43;
            if (1 <= $rang && 43 >= $rang){
                return round(250 * 0.955 ** ($rang - 1));
            }

            // Platz 44 bis Rest
            return max([round(250 * 0.955 ** (43) * 0.97 ** ($rang - 1 - 43)), 15]);
        } elseif ($saison >= 20) {
            if (1 <= $rang && 13 >= $rang) {
                return round(200 - (($rang - 1) * 8));
            } 
            if (14 <= $rang && 25 >= $rang) {
                return round(104 - (($rang - 13) * 4));
            }
            if (26 <= $rang && 37 >= $rang) {
                return round(56 - (($rang - 25) * 2));
            }
            return max([round(31 - ($rang - 38)), 20]);
        } elseif ($saison >= 16) {
            if (1 <= $rang && 8 >= $rang) {
                return round(150 - (($rang - 1) * 2));
            } 
            if (9 <= $rang && 16 >= $rang) {
                return round(118 - (($rang - 9) * 2));
            }
            if (17 <= $rang && 24 >= $rang) {
                return round(90 - (($rang - 17) * 2));
            }
            if (25 <= $rang && 32 >= $rang) {
                return round(66 - (($rang - 25) * 2));
            }
            return max([round(46 - (($rang - 33) * 2)), 6]);
        } elseif ($saison >= 13) {
            if (1 <= $rang && 6 >= $rang) {
                return round(134 - (($rang - 1) * 2));
            } 
            if (7 <= $rang && 12 >= $rang) {
                return round(114 - (($rang - 7) * 2));
            }
            if (13 <= $rang && 18 >= $rang) {
                return round(96 - (($rang - 13) * 2));
            }
            if (19 <= $rang && 24 >= $rang) {
                return round(66 - (($rang - 19) * 2));
            }
            return max([round(46 - (($rang - 25) * 2)), 2]);
        } elseif ($saison >= 8) {
            return max([round(132 - (($rang - 1) * 2)), 20]);
        }

        return NULL;
    }

    /**
     * Weist dem Platz in der Rangtabelle einen Block zu
     *
     * @param int|null $rang
     * @return string|null
     */
    public static function rang_to_block(?int $rang, int $saison): ?string
    {
        $zuordnung = self::get_block_zuordnung($saison);
        
        // Nichtligateam
        if (is_null($rang)) return NULL;

        // Blockzuordnung
        foreach ($zuordnung as $block => $range) {
            if ($range[0] <= $rang && $range[1] >= $rang){
                return $block;
            }
        }
    }

    public static function get_block_zuordnung(int $saison)
    {
        if ($saison >= 22) {
            $zuordnung = [
                "A" => [1, 6],
                "AB" => [7, 13],
                "BC" => [14, 21],
                "CD" => [22, 31],
                "DE" => [32, 43],
                "EF" => [44, 57],
                "F" => [58, INF]
            ];
        } elseif ($saison >= 20) {
            $zuordnung = [
                "A" => [1, 6],
                "AB" => [7, 12],
                "BC" => [13, 18],
                "CD" => [19, 24],
                "DE" => [25, 30],
                "EF" => [31, 36],
                "F" => [37, INF]
            ];
        } elseif ($saison >= 16) {
            $zuordnung = [
                "A" => [1, 8],
                "AB" => [9, 16],
                "BC" => [17, 24],
                "CD" => [25, 32],
                "DE" => [33, 40],
                "E" => [41, INF]
            ];
        } elseif ($saison >= 13) {
            $zuordnung = [
                "A" => [1, 6],
                "AB" => [7, 12],
                "BC" => [13, 18],
                "CD" => [19, 24],
                "DE" => [25, 30],
                "E" => [31, INF]
            ];
        } elseif ($saison >= 9) {
            $zuordnung = [
                "A" => [1, 6],
                "AB" => [7, 12],
                "BC" => [13, 18],
                "CD" => [19, 24],
                "D" => [25, INF]
            ];
        } elseif ($saison >= 0) {
            $zuordnung = [
                "" => [1, INF]
            ];
        }

        return $zuordnung;
    }
}