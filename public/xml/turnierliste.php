<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../logic/first.logic.php'; //autoloader und Session

//Assoziatives Array aller Turniere der Aktuellen Saison
 $turniere = Turnier::get_all_turniere("WHERE saison='".Config::SAISON."'");

$xml = new SimpleXMLElement('<turniere/>');

xml::array_to_xml($turniere,$xml,"turnier");

Header('Content-type: text/xml');
print($xml->asXML());
