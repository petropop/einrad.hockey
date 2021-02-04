<?php

/**
 * Class Spielplan
 *
 * Verwaltet Spielpläne, berechnet Turniertabellen und schreibt Turnierergebnisse in die DB.
 */
class Spielplan
{
    public int $turnier_id;
    public Turnier $turnier;
    public array $teamliste;
    public int $anzahl_teams;
    public int $anzahl_spiele;
    public array $platzierungstabelle = [];
    public array $direkter_vergleich_tabellen = [];
    public array $tore_tabelle;
    public array $turnier_tabelle;
    public array $details;
    /**
     * @var array|array[]
     * gesamt => Alle Spiel-Ids für Penaltys
     * ausstehend => Ausstehende Spiel-IDs für Penaltys
     * gesamt => Team-ids der ausstehenden Penaltys
     */
    public array $penaltys = [
        'gesamt' => [],
        'ausstehend' => [],
        'kontrolle' => []
    ];
    public array $spiele;
    public bool $out_of_scope = false;
    public array $penalty_tabellen = [];

    /**
     * Spielplan constructor.
     *
     * @param Turnier $turnier
     * @param bool $penaltys Penaltys werden ignoriert. Dies ist für eine zweite Instanz der Klasse, aus welcher die
     * gesamt zu spielenden Penaltys in Erfahrung gebracht werden.
     */
    function __construct(Turnier $turnier, bool $penaltys = true)
    {
        $this->turnier_id = $turnier->turnier_id;
        $this->turnier = $turnier;
        $this->teamliste = $this->turnier->get_liste_spielplan();
        $this->anzahl_teams = count($this->teamliste);
        $this->anzahl_spiele = $this->anzahl_teams - 1;
        $this->details = self::get_details();
        $this->spiele = $this->get_spiele();
        $this->tore_tabelle = $this->get_toretabelle($penaltys);
        $this->turnier_tabelle = self::get_sorted_turniertabelle($this->tore_tabelle);
        $this->set_platzierungen($this->tore_tabelle);
        $this->set_wertigkeiten();
    }

    /**
     * Holt sich die Spielplandetails aus der DB
     *
     * @return array Array der Details
     */
    public function get_details(): array
    {
        $plaetze = $this->anzahl_teams;
        $spielplan = $this->turnier->details["spielplan"];
        $sql = "
                SELECT * 
                FROM spielplan_details 
                WHERE plaetze = '$plaetze' 
                AND spielplan = '$spielplan'
                ";
        $result = db::read($sql);
        return db::escape(mysqli_fetch_assoc($result));
    }

    /**
     * Gibt ein Array der Spiele aus dem in der Datenbank hinterlegten Spielplan
     *
     * @return array
     */
    public function get_spiele(): array
    {
        $startzeit = strtotime($this->turnier->details["startzeit"]);
        $spielzeit = ($this->details["anzahl_halbzeiten"] * $this->details["halbzeit_laenge"]
                + $this->details["pause"]) * 60; // In Sekunden für Unixzeit
        $sql = "
                SELECT spiel_id, team_id_a, t1.teamname AS teamname_a, team_id_b, t2.teamname AS teamname_b,
                schiri_team_id_a, schiri_team_id_b, tore_a, tore_b, penalty_a, penalty_b
                FROM spiele sp, teams_liga t1, teams_liga t2
                WHERE turnier_id = $this->turnier_id
                AND team_id_a = t1.team_id
                AND team_id_b = t2.team_id
                ORDER BY spiel_id
                ";
        $result = db::read($sql);
        while ($spiel = mysqli_fetch_assoc($result)) {
            $spiel["zeit"] = date("H:i", $startzeit);
            $spiele[$spiel['spiel_id']] = $spiel;
            $extra_pause = ($this->details['plaetze'] == 4 && !($spiel['spiel_id'] % 2)) ? (30 * 60) : 0;
            // 4er Spielplan Extrapause nach geraden Spielen
            $startzeit += $spielzeit + $extra_pause;
        }
        return db::escape($spiele ?? []);
    }

    /**
     * Schreibt ein Spielergebnis in die Datenbank
     *
     * @param int $spiel_id
     * @param string $tore_a
     * @param string $tore_b
     * @param string $penalty_a
     * @param string $penalty_b
     */
    public function set_spiele(int $spiel_id, string $tore_a, string $tore_b, string $penalty_a, string $penalty_b)
    {
        // Damit die nicht eingetragene Tore nicht als 0 : 0 gewertet werden, müssen sie NULL sein
        $tore_a = !is_numeric($tore_a) ? 'NULL' : $tore_a;
        $tore_b = !is_numeric($tore_b) ? 'NULL' : $tore_b;
        $penalty_a = !is_numeric($penalty_a) ? 'NULL' : $penalty_a;
        $penalty_b = !is_numeric($penalty_b) ? 'NULL' : $penalty_b;

        $sql = "
                UPDATE spiele 
                SET tore_a = $tore_a, tore_b = $tore_b, penalty_a = $penalty_a, penalty_b = $penalty_b
                WHERE turnier_id = $this->turnier_id AND spiel_id = $spiel_id
                ";
        db::write($sql);
    }

    /**
     * Gibt eine Tabelle der Spielergebnisse der Teams untereinander aus.
     * Für den direkten Vergleich wichtig.
     *
     * @param bool $penaltys Mit oder ohne Penalty-Ergebnissen
     * @return array Torematrix aller Teams untereinander
     */
    public function get_toretabelle($penaltys = true): array
    {
        foreach ($this->spiele as $spiel) {

            if (!$penaltys) $spiel['penalty_a'] = $spiel['penalty_b'] = NULL;

            $tore_tabelle[$spiel['team_id_a']][$spiel['team_id_b']] =
                [
                    'tore' => $spiel['tore_a'],
                    'gegentore' => $spiel['tore_b'],
                    'penalty_tore' => $spiel['penalty_a'],
                    'penalty_gegentore' => $spiel['penalty_b'],
                ];
            $tore_tabelle[$spiel['team_id_b']][$spiel['team_id_a']] =
                [
                    'tore' => $spiel['tore_b'],
                    'gegentore' => $spiel['tore_a'],
                    'penalty_tore' => $spiel['penalty_b'],
                    'penalty_gegentore' => $spiel['penalty_a'],
                ];
        }
        return $tore_tabelle ?? [];
    }

    /**
     * Sortiert die Turniertabelle
     *
     * @param array $tore_tabelle Toretabelle aus get_toretabelle
     * @return array Sortierte Turniertabelle
     */
    public static function get_sorted_turniertabelle(array $tore_tabelle): array
    {
        $sort_function = function ($ergebnis_a, $ergebnis_b) {
            if ($ergebnis_a['punkte'] > $ergebnis_b['punkte']) return -1;
            if ($ergebnis_a['punkte'] < $ergebnis_b['punkte']) return 1;
            if ($ergebnis_a['tordifferenz'] > $ergebnis_b['tordifferenz']) return -1;
            if ($ergebnis_a['tordifferenz'] < $ergebnis_b['tordifferenz']) return 1;
            if ($ergebnis_a['tore'] > $ergebnis_b['tore']) return -1;
            if ($ergebnis_a['tore'] < $ergebnis_b['tore']) return 1;
            if ($ergebnis_a['penalty_punkte'] > $ergebnis_b['penalty_punkte']) return -1;
            if ($ergebnis_a['penalty_punkte'] < $ergebnis_b['penalty_punkte']) return 1;
            if ($ergebnis_a['penalty_diff'] > $ergebnis_b['penalty_diff']) return -1;
            if ($ergebnis_a['penalty_diff'] < $ergebnis_b['penalty_diff']) return 1;
            if ($ergebnis_a['penalty_tore'] > $ergebnis_b['penalty_tore']) return -1;
            if ($ergebnis_a['penalty_tore'] < $ergebnis_b['penalty_tore']) return 1;
            return -1; // Team welches links steht kommt nach oben, also das Team mit der höheren Rangtabellenwertung
        };
        $turnier_tabelle = self::get_turniertabelle($tore_tabelle);
        uasort($turnier_tabelle, $sort_function);
        return $turnier_tabelle;
    }

    /**
     * Erstellt eine Turniertabelle mit Punkten, Tordifferenz, etc.
     *
     * @param array $tore_tabelle Toretabelle aus get_toretabelle()
     * @return array unsortierte Turniertabelle
     */
    private static function get_turniertabelle(array $tore_tabelle): array
    {
        // Punkte zählen
        foreach ($tore_tabelle as $team_id => $team_spiele) {
            $punkte = $tordifferenz = $gegentore = $tore = $penalty_diff = $penalty_tore = $penalty_gegentore = $penalty_punkte = NULL;
            $spiele = $penalty_spiele = 0;
            foreach ($team_spiele as $spiel) {
                if (is_null($spiel['tore']) or is_null($spiel['gegentore'])) continue;
                $punkte += ($spiel['tore'] > $spiel['gegentore']) ? 3 : 0;
                $punkte += ($spiel['tore'] == $spiel['gegentore']) ? 1 : 0;
                $tordifferenz += $spiel['tore'] - $spiel['gegentore'];
                $tore += $spiel['tore'];
                $gegentore += $spiel['gegentore'];
                $spiele += 1;

                if (is_null($spiel['penalty_tore']) or is_null($spiel['penalty_gegentore'])) continue;
                $penalty_punkte += ($spiel['penalty_tore'] > $spiel['penalty_gegentore']) ? 3 : 0;
                $penalty_punkte += ($spiel['penalty_tore'] == $spiel['penalty_gegentore']) ? 1 : 0;
                $penalty_diff += $spiel['penalty_tore'] - $spiel['penalty_gegentore'];
                $penalty_tore += $spiel['penalty_tore'];
                $penalty_gegentore += $spiel['penalty_gegentore'];
                $penalty_spiele += 1;
            }
            // Turniertabelle beschreiben
            $turnier_tabelle[$team_id] =
                [
                    'spiele' => $spiele,
                    'punkte' => $punkte,
                    'penalty_spiele' => $penalty_spiele,
                    'tordifferenz' =>  $tordifferenz,
                    'tore' => $tore,
                    'gegentore' => $gegentore,
                    'penalty_punkte' => $penalty_punkte,
                    'penalty_diff' => $penalty_diff,
                    'penalty_tore' => $penalty_tore,
                    'penalty_gegentore' => $penalty_gegentore
                ];
        }
        return $turnier_tabelle ?? [];
    }

    public function set_platzierungen($tore_tabelle)
    {
        $turnier_tabelle = self::get_sorted_turniertabelle($tore_tabelle); // neue Turniertabelle erstellen
        // Mit dem ersten Team gleichplatzierte Teams suchen
        $first_team_id = array_key_first($turnier_tabelle);
        $gleichplatzierte_teams = $this->get_gleichplatzierte_teams($turnier_tabelle, $first_team_id, "erster_vergleich");

        // Fall 1: Team ist eindeutig platzierbar, da das erste Team in der sortierten Turniertabelle
        // nur mit sich selbst gleichplatziert ist.
        if (count($gleichplatzierte_teams) === 1) {
            $this->set_platzierung($first_team_id);
            self::remove_team_ids($tore_tabelle, [$first_team_id]); // Werden aus der Toretabelle entfernt
            if (count($tore_tabelle) != 0) self::set_platzierungen($tore_tabelle);
        } else {
            // Direkter Vergleich mit nur den gleichplatzierten Teams in den nicht-ersten Vergleich
            $tore_tabelle_gleiche_teams = self::filter_team_ids($tore_tabelle, $gleichplatzierte_teams);
            self::direkter_vergleich($tore_tabelle_gleiche_teams, true);

            // Forführung des ersten Vergleichs ohne die gleichplatzierten Teams
            self::remove_team_ids($tore_tabelle, $gleichplatzierte_teams);
            if (count($tore_tabelle) != 0) self::set_platzierungen($tore_tabelle);
        }
        if (count($tore_tabelle) == 0){ // Zuletzt werden die noch zu spielenden Penaltys ermittelt
            foreach($this->penaltys['gesamt'] as $spiel_id){
                if(
                    is_null($this->spiele[$spiel_id]['penalty_a'])
                    or is_null($this->spiele[$spiel_id]['penalty_b'])
                ){
                    $this->penaltys['ausstehend'][] = $spiel_id;
                }
            }
        }
    }

    public function direkter_vergleich($tore_tabelle, $print = false)
    {
        // Fall 0: Nur ein Team verblieben
        if (count($tore_tabelle) == 1){
            $this->set_platzierung(array_key_first($tore_tabelle));
            return;
        }
        $turnier_tabelle = self::get_sorted_turniertabelle($tore_tabelle); // neue Turniertabelle erstellen
        // Direktervergleich Tabelle ausgeben
        if ($print && $this->check_ergebnis_fix(array_keys($tore_tabelle)))
            $this->direkter_vergleich_tabellen[] = $turnier_tabelle;

        // Mit dem ersten Team gleichplatzierte Teams suchen
        $first_team_id = array_key_first($turnier_tabelle);
        $gleichplatzierte_teams = $this->get_gleichplatzierte_teams($turnier_tabelle, $first_team_id, "direkter_vergleich");

        // Fall 1: Team ist eindeutig platzierbar, da das erste Team in der sortierten Turniertabelle
        // nur mit sich selbst gleichplatziert ist.
        if (count($gleichplatzierte_teams) === 1) {
            $this->set_platzierung($first_team_id);
            self::remove_team_ids($tore_tabelle, [$first_team_id]); // Werden aus der Toretabelle entfernt
            if (count($tore_tabelle) != 0) self::direkter_vergleich($tore_tabelle);
            return;
        }
        // Fall 2: Team ist nicht eindeutig platzierbar, es muss ein neuer direkter Vergleich mit Untertabelle erstellt werden
        if (count($gleichplatzierte_teams) < count($turnier_tabelle)) {
            // Toretabelle mit nur den gleichplatzierten Teams in den nicht-ersten Vergleich
            $tore_tabelle_gleiche_teams = self::filter_team_ids($tore_tabelle, $gleichplatzierte_teams);
            self::direkter_vergleich($tore_tabelle_gleiche_teams, true);
            // Toretabelle ohne die gleichplatzierten Teams
            self::remove_team_ids($tore_tabelle, $gleichplatzierte_teams);
            if (count($tore_tabelle) != 0) self::direkter_vergleich($tore_tabelle);
            return;
        }
        // Fall 3:
        // Tabelle besteht nur aus gleichplatzierten Teams also ab in den Penalty-Vergleich
        // Mit einer Tortabelle, in welcher nur die Spiele der gleichplatzierten Teams gezählt werden
        $tore_tabelle_gefiltert = self::filter_team_ids($tore_tabelle, $gleichplatzierte_teams);
        if ($tore_tabelle != $tore_tabelle_gefiltert){
            self::direkter_vergleich($tore_tabelle_gefiltert, true);
        }else{
            if ($this->check_ergebnis_fix($gleichplatzierte_teams))
                $this->penaltys['gesamt'] =
                    array_merge($this->penaltys['gesamt'], $this->get_spiel_ids($gleichplatzierte_teams));
            self::penalty_vergleich($tore_tabelle, true);
        }
    }

    public function penalty_vergleich($tore_tabelle, $print = false)
    {
        // Fall 0: Nur ein Team verblieben
        if (count($tore_tabelle) == 1){
            $this->set_platzierung(array_key_first($tore_tabelle));
            return;
        }
        // neue Turniertabelle erstellen und ggf ausgeben
        $turnier_tabelle = self::get_sorted_turniertabelle($tore_tabelle);
        if ($print && $this->check_ergebnis_fix(array_keys($turnier_tabelle)))
            $this->penalty_tabellen[] = $turnier_tabelle;
        // Mit dem ersten Team gleichplatzierte Teams suchen
        $first_team_id = array_key_first($turnier_tabelle);
        $gleichplatzierte_teams = $this->get_gleichplatzierte_teams($turnier_tabelle, $first_team_id, "penalty_vergleich");
        if (count($gleichplatzierte_teams) === 1) {
            $this->set_platzierung($first_team_id);
            self::remove_team_ids($tore_tabelle, [$first_team_id]); // Werden aus der Toretabelle entfernt
            if (count($tore_tabelle) != 0) self::penalty_vergleich($tore_tabelle);
            return;
        }

        // Fall 2: Team ist nicht eindeutig platzierbar, es kann ein neuer Vergleich mit Untertabelle erstellt werden
        if (count($gleichplatzierte_teams) < count($turnier_tabelle)) {
            // Tabelle mit nur den gleichplatzierten Teams und deren Spiele
            $tore_tabelle_gleiche_teams = self::filter_team_ids($tore_tabelle, $gleichplatzierte_teams);
            self::penalty_vergleich($tore_tabelle_gleiche_teams, true);
            // Tabelle ohne die gleichplatzierten Teams
            self::remove_team_ids($tore_tabelle, $gleichplatzierte_teams);
            if (count($tore_tabelle) != 0) self::penalty_vergleich($tore_tabelle);
            return;
        }

        // Fall 3: Team ist nicht eindeutig platzierbar, ein neuer Vergleich ändert nichts
        if (self::filter_team_ids($tore_tabelle, $gleichplatzierte_teams) != $tore_tabelle) {
            $this->penalty_vergleich(self::filter_team_ids($tore_tabelle, $gleichplatzierte_teams), true);
        } else {
            if ($this->check_ergebnis_fix($gleichplatzierte_teams))
                $this->penaltys['kontrolle'] = $this->get_spiel_ids($gleichplatzierte_teams);
            // Eine weitere Sortierung ist nicht mehr möglich, Penaltys müssen gespielt werden
            foreach ($gleichplatzierte_teams as $team_id) {
                $this->set_platzierung($team_id);
            }
        }
    }

    /**
     * Gibt ein Array der team_ids von mit einem Team gleichplatzierten Teams.
     * Gibt nur eine team_id aus, wenn das übergebene Team nur mit sich selbst gleichplatziert ist,
     * also eindeutig Platzierbar ist.
     *
     * @param array $turnier_tabelle Turniertabelle als Grundlage für Gleichplatzierung
     * @param int $team_id Team-ID des Teams, nach dem gleichplatzierte Teams gesucht werden sollen
     * @param string $art Um welche art des Vergleiches handelt es sich?
     * @return array Array der team_ids
     */
    private function get_gleichplatzierte_teams(array $turnier_tabelle, int $team_id, $art = 'erster_vergleich'): array
    {
        $match = $turnier_tabelle[$team_id];
        unset($match['spiele'], $match['penalty_spiele']);
            // Anzahl der Spiele und Anzahl der Penaltys sollen nicht berücksichtigt werden.
        if ($art == 'erster_vergleich'){
            $function = function ($value) use ($match) {
                return $value['punkte'] == $match['punkte'];
            };
        }elseif($art == 'direkter_vergleich'){
            $function = function ($value) use ($match) {
                return ($value['punkte'] == $match['punkte']
                        && $value['tordifferenz'] == $match['tordifferenz']
                        && $value['tore'] == $match['tore']);
            };
        }else{
            $function = function ($value) use ($match) {
                return ($value['penalty_punkte'] == $match['penalty_punkte']
                    && $value['penalty_diff'] == $match['penalty_diff']
                    && $value['penalty_tore'] == $match['penalty_tore']);
            };
        }

        return array_keys(array_filter($turnier_tabelle, $function)); // Wenn es mehrere gleiche Teams gibt: false
    }

    /**
     * Platziert ein Team in $this->platzierungstabelle
     *
     * @param int $team_id Team-ID des Teams, welches platziert werden soll
     */
    private function set_platzierung(int $team_id)
    {
        $this->platzierungstabelle[$team_id] =
            [
                'platz' => count($this->platzierungstabelle) + 1,
                'teamname' => $this->teamliste[$team_id]['teamname'],
                'ligapunkte' => 0,
                'statistik' => $this->turnier_tabelle[$team_id],
            ];
    }

    /**
     * Fügt die Teamwertigkeiten in die Platzeriungstabelle ein.
     */
    public function set_wertigkeiten()
    {
        $reverse_tabelle = array_reverse($this->platzierungstabelle, true);

        $highest_ligateam = function () use ($reverse_tabelle) {
            foreach ($reverse_tabelle as $team_id => $eintrag) {
                if ($this->teamliste[$team_id]['wertigkeit'] !== 'NL') return $this->teamliste[$team_id]['wertigkeit'];
            }
            return 0;
        };

        $ligapunkte = 0;
        foreach ($reverse_tabelle as $team_id => $eintrag) {
            $wert = $this->teamliste[$team_id]['wertigkeit'];
            $wert = ($wert === 'NL') ? max($werte ?? [max(round($highest_ligateam() / 2) - 1, 14)]) + 1 : $wert;
            $werte[] = $wert;
            $ligapunkte += $wert;
            $this->platzierungstabelle[$team_id]['ligapunkte'] = round($ligapunkte * 6 / $this->details['faktor']);
        }
    }

    /**
     * Entfernt Teams aus der Toretabelle, nicht jedoch aus Unter-Torebegegnungs-Tabelle
     *
     * @param array $tore_tabelle Toretabelle als Grundlage
     * @param array $team_ids Teams die Entfernt werden
     */
    private static function remove_team_ids(array &$tore_tabelle, array $team_ids)
    {
        foreach ($team_ids as $team_id) {
            unset($tore_tabelle[$team_id]);
        }
    }

    /**
     * Filtert eine Toretabelle nach Teams und erstellt so eine Untertabelle für den direkten Vergleich mit nur
     * noch diesen Teams und Begegnungen dieser Teams untereinander.
     *
     * @param array $tore_tabelle Toretabelle als Grundlage
     * @param array $team_ids Liste an TeamIDs nach welchen
     * @return array Neue Toretabelle mit noch den Team-IDs wird zurückgegeben.
     */
    private static function filter_team_ids(array $tore_tabelle, array $team_ids): array
    {
        $filter_function = function ($team_id) use ($team_ids) {
            return in_array($team_id, $team_ids); // Alle Team-IDs, bis auf die Übergebenen, werden entfernt
        };
        foreach ($tore_tabelle as &$ergebnis) {
            $ergebnis = array_filter($ergebnis, $filter_function, ARRAY_FILTER_USE_KEY);
        }
        return array_filter($tore_tabelle, $filter_function, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Ist die Penaltybegegnung unvermeidbar?
     * @param array $team_ids Array der Penalty-Teams
     * @return bool
     */
    public function check_ergebnis_fix(array $team_ids): bool
    {
        // Hilfsfunktion für erreichbare Punkte eines Teams
        $vergleich = function ($team_id) {
            $return['punkte_min'] = $this->turnier_tabelle[$team_id]['punkte'];
            $return['punkte_max'] =
                $return['punkte_min'] + ($this->anzahl_spiele - $this->turnier_tabelle[$team_id]['spiele']) * 3;
            $return['nicht_erreichbar'] = $return['punkte_max'] - 1;
            return $return;
        };

        foreach ($team_ids as $team_id) {
            if ($this->turnier_tabelle[$team_id]['spiele'] < $this->anzahl_spiele) {
                return false; // Penaltybegegnung  vermeidbar, da noch nicht alle Spiele vom Team absolviert
            }
            $punkte_pen_team = $this->turnier_tabelle[$team_id]['punkte'];
            foreach (array_keys($this->turnier_tabelle) as $vgl_team_id) {
                if ($team_id == $vgl_team_id) continue; // Nicht mit sich selbst vergleichen
                // Penaltybegnung vermeidbar, da ein Team die Punktzahl des Penalty-Teams noch erreichen könnte?
                if ($punkte_pen_team < $vergleich($vgl_team_id)['punkte_max']
                    && $punkte_pen_team > $vergleich($vgl_team_id)['punkte_min']
                    && $punkte_pen_team != $vergleich($vgl_team_id)['nicht_erreichbar']) return false;
            } // foreach Teams auf dem Turnier
        } // foreach Penalty-Teams
        return true;
    }
//    public function get_erreichbare_plaetze($team_id){
/*        $spielplan = new Spielplan ($turnier_id);
        foreach($spielplan->spiele as $spiel_id => $spiel){
            if ($spiel['team_id_a'] == $team_id){
                if (is_null($spiel['tore_a']) $spielplan->spiele[$spiel_id]['tore_a'] = 127;
                if (is_null($spiel['penalty_a']) $spielplan->spiele[$spiel_id]['penalty_a'] = 127;
                if (is_null($spiel['tore_b']) $spielplan->spiele[$spiel_id]['tore_b'] = 0;
                if (is_null($spiel['penalty_b']) $spielplan->spiele[$spiel_id]['penalty_b'] = 0;
            }
            if ($spiel['team_id_b'] == $team_id){
                if (is_null($spiel['tore_a']) $spielplan->spiele[$spiel_id]['tore_a'] = 0;
                if (is_null($spiel['penalty_a']) $spielplan->spiele[$spiel_id]['penalty_a'] = 0;
                if (is_null($spiel['tore_b']) $spielplan->spiele[$spiel_id]['tore_b'] = 127;
                if (is_null($spiel['penalty_b']) $spielplan->spiele[$spiel_id]['penalty_b'] = 127;
            }
        }
            unset($spielplan->platzierungstabelle);
            $spielplan->set_platzierungen($spielplan->get_toretabelle());
            $max_platz = $spielplan->platzierungstabelle[$team_id]['platz']);
        foreach($spielplan->spiele as $spiel_id => $spiel){
            if ($spiel['team_id_a'] == $team_id){
                if (is_null($spiel['tore_a']) $spielplan->spiele[$spiel_id]['tore_a'] = 0;
                if (is_null($spiel['penalty_a']) $spielplan->spiele[$spiel_id]['penalty_a'] = 0;
                if (is_null($spiel['tore_b']) $spielplan->spiele[$spiel_id]['tore_b'] = 127;
                if (is_null($spiel['penalty_b']) $spielplan->spiele[$spiel_id]['penalty_b'] = 127;
            }
            if ($spiel['team_id_b'] == $team_id){
                if (is_null($spiel['tore_a']) $spielplan->spiele[$spiel_id]['tore_a'] = 127;
                if (is_null($spiel['penalty_a']) $spielplan->spiele[$spiel_id]['penalty_a'] = 127;
                if (is_null($spiel['tore_b']) $spielplan->spiele[$spiel_id]['tore_b'] = 0;
                if (is_null($spiel['penalty_b']) $spielplan->spiele[$spiel_id]['penalty_b'] = 0;
            }
            unset($spielplan->platzierungstabelle);
            $spielplan->set_platzierungen($spielplan->get_toretabelle());
            $min_platz = $spielplan->platzierungstabelle[$team_id]['platz']);
        }*/
//        // Hilfsfunktion für erreichbare Punkte eines Teams
//        $vergleich = function ($team_id) {
//            $return['punkte_min'] = $this->turnier_tabelle[$team_id]['punkte'];
//            $return['punkte_max'] =
//                $return['punkte_min'] + ($this->anzahl_spiele - $this->turnier_tabelle[$team_id]['spiele']) * 3;
//            return $return;
//        };
//        $max_punkte = $vergleich($team_id)['punkte_max'];
//        $min_punkte = $this->turnier_tabelle[$team_id]['punkte'];
//        $bester_platz = $this->anzahl_teams;
//        $letzter_platz = 1;
//        foreach (array_keys($this->turnier_tabelle) as $key => $vgl_team_id) {
//            // Welche Plätze kann as Team noch erreichen?
//            if ($vergleich($vgl_team_id)['punkte_min'] <= $max_punkte) $bester_platz = min([$bester_platz, $key + 1]);
//            if ($vergleich($vgl_team_id)['punkte_max'] >= $min_punkte) $letzter_platz = max([$letzter_platz, $key + 1]);
//        } // foreach Teams auf dem Turnier
//        db::debug($bester_platz.$letzter_platz);
//        return ($bester_platz == $letzter_platz) ? $bester_platz : $bester_platz . '-' . $letzter_platz;
//    }


    /**
     * Erstellt einen Spielplan in der Datenbank
     *
     * @param Turnier $turnier
     * @return bool Erfolgreich / Nicht erfolgreich estellt
     */
    public static function set_spielplan(Turnier $turnier): bool
    {
        $spielplan_art = $turnier->details["spielplan"];
        $teamliste = $turnier->get_liste_spielplan();
        $anzahl_teams = count($teamliste);

        // Teamlisten-Array mit 1 Beginnen lassen zum Ausfüllen der Spielplan-Vorlage
        $teamliste = array_values($teamliste);
        array_unshift($teamliste, '');
        unset($teamliste[0]);

        // Spielplanvorlage aus der Datenbank
        $sql = "
                SELECT * 
                FROM spielplan_paarungen 
                WHERE plaetze = '$anzahl_teams' 
                AND spielplan = '$spielplan_art'
                ";
        $result = db::read($sql);

        while ($spiel = mysqli_fetch_assoc($result)) {
            $sql_inserts[] = "("
                . $turnier->turnier_id . "," . $spiel["spiel_id"] . ","
                . $teamliste[$spiel["team_a"]]["team_id"] . ","
                . $teamliste[$spiel["team_b"]]["team_id"] . ","
                . $teamliste[$spiel["schiri_a"]]["team_id"] . ","
                . $teamliste[$spiel["schiri_b"]]["team_id"] . ", "
                . "NULL, NULL, NULL, NULL)";
        }
        if (!isset($sql_inserts)) {
            Form::error("Es konnte keine Spielreihenfolge aus dem Spielplan ermittelt werden");
            return false;
        }

        // Eventuell alten Spielpläne löschen
        Spielplan::delete_spielplan($turnier);

        // Neuen Spielplan erstellen
        $sql = "
                INSERT INTO spiele 
                VALUES " . implode(', ', $sql_inserts) . "
                ";
        db::write($sql);

        // Turnierlog
        $turnier->log("Dynamischer" . $anzahl_teams . "er JgJ-Spielplan erstellt.");
        $turnier->set_phase('spielplan');
        return true;
    }

    /**
     * Löscht einen bisher erstellten Spielplan
     *
     * @param Turnier $turnier
     */
    public static function delete_spielplan(Turnier $turnier)
    {
        // Es existiert kein dynamischer Spielplan
        if (!self::check_exist($turnier->turnier_id)) return;

        // Spielplan löschen
        $sql = "
                DELETE FROM spiele 
                WHERE turnier_id = $turnier->turnier_id
                ";
        db::write($sql);
        $turnier->log("Dynamischer JgJ-Spielplan gelöscht.");
        $turnier->set_phase('melde');
    }

    /**
     * Existiert ein automatisch erstellter Spielplan in der Datenbank?
     *
     * @param int $turnier_id
     * @return bool
     */
    public static function check_exist(int $turnier_id): bool
    {
        $sql = "
                SELECT *
                FROM spiele
                WHERE turnier_id = $turnier_id;
                ";
        return db::read($sql)->num_rows > 0;
    }

    /**
     * Überprüft ob Penaltyfelder zum Eintragen freigegeben werden.
     *
     * @param int $spiel_id
     * @param bool $ausstehend Ausstehende oder allgemeine Penalty-Teams?
     * @return bool True, wenn ein Penalty gespielt werden muss.
     */
    public function check_penalty_spiel(int $spiel_id, bool $ausstehend = false): bool
    {
        $penaltys = ($ausstehend) ? $this->penaltys['ausstehend'] : $this->penaltys['gesamt'];
        return in_array($spiel_id, $penaltys);
    }

    public function check_penalty_team(int $team_id): bool
    {
        foreach ($this->penaltys['ausstehend'] as $spiel_id) {
            if (
                $this->spiele[$spiel_id]['team_id_a'] == $team_id
                or $this->spiele[$spiel_id]['team_id_b'] == $team_id
            ) return true;
        }
        return false;
    }

    /**
     * Penaltyspalte nur Anzeigen, wenn auch Penaltys gespielt werden müssen
     *
     * @return bool True, wenn Penaltys vorhanden sind.
     */
    public function check_penalty_anzeigen(): bool
    {
        if (!$this->validate_penalty_ergebnisse()) return true;
        return !empty($this->penaltys['gesamt']);
    }

    /**
     * Gibt den String der Penaltywarnung der austehenden Penaltys aus.
     *
     * @return string
     */
    public function get_penalty_warnung(): string
    {
        foreach ($this->penaltys['ausstehend'] as $spiel_id) {
            $penaltys[] = $this->spiele[$spiel_id]['teamname_a'] . ' | ' . $this->spiele[$spiel_id]['teamname_b'];
        }
        return implode('<br>', $penaltys ?? []);
    }

    /**
     * Überprüft, ob Penalty-Ergebnisse bei den richtigen Teams eingetragen worden sind.
     *
     * @return bool
     */
    public function validate_penalty_ergebnisse(): bool
    {
        foreach ($this->spiele as $spiel_id => $spiel) {
            if ((!is_null($spiel['penalty_a']) or !is_null($spiel['penalty_b']))
                && !in_array($spiel_id, $this->penaltys['gesamt'])
            ) return false;
            // Es wurde also ein Penalty bei einem Spiel eingetragen, bei welchem kein Penalty vorgesehen ist.
        }
        return true;
    }

    /**
     * Check, ob das Turnier beendet wurde
     *
     * @return bool true, wenn keine Spiele und Penalty-Begegnungen ausstehen.
     */
    function check_turnier_beendet(): bool
    {
        if (!empty($this->penaltys['ausstehend'])) return false;
        $min_spiele = min(array_column($this->turnier_tabelle, 'spiele'));
        // kleinsten Anzahl an beendeten Spielen eines Teams
        return $this->anzahl_spiele == $min_spiele;
    }

    /**
     * Check, ob jedes Team ein Team gespielt hat.
     * Wenn ja, wird die Platzierung und das Turnierergebnis im Template angezeigt
     *
     * @return bool true, wenn alle mind. ein Spiel gespielt haben
     */
    function check_tabelle_einblenden(): bool
    {
        // Team mit der kleinsten Anzahl an Spielen hat mehr als 0 Spiele vollendet
        return 0 < min(array_column($this->turnier_tabelle, 'spiele'));
    }

    public function get_spiel_ids(array $team_ids): array
    {
        foreach ($this->spiele as $spiel_id => $spiel) {
            if (in_array($spiel['team_id_a'], $team_ids) && in_array($spiel['team_id_b'], $team_ids))
                $return[] = $spiel_id;
        }
        return $return ?? [];
    }

}
