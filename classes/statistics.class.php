<?php

class Statistics{
    
    public $saison = Config::SAISON;
    public $turniere;
    public $spiele;
    public $punkte;

    function __construct() {
        $this->turniere = $this->get_aktuelle_turniere();
        $this->spiele = $this->get_aktuelle_spiele();
        $this->punkte = $this->get_aktuelle_punkte();
    }

    function get_aktuelle_turniere() {
        $sql = "SELECT COUNT(*) AS turniere FROM `turniere_liga` WHERE saison = ". $this->saison . "";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result['turniere'];
    }

    function get_aktuelle_spiele() {
        $sql = "SELECT COUNT(*) AS spiele FROM `spiele` sp, `turniere_liga` tur WHERE sp.turnier_id = tur.turnier_id AND tur.saison = " . $this->saison . "";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result['spiele'];
    }

    function get_aktuelle_punkte() {
        $sql = "SELECT SUM(ergebnis) AS punkte FROM `turniere_ergebnisse` te, `turniere_liga` tl WHERE te.turnier_id = tl.turnier_id AND tl.saison = " . $this->saison . "";
        $result = db::readdb($sql);
        $result = mysqli_fetch_assoc($result);

        return $result['punkte'];
    }

}