<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../logic/first.logic.php'; //autoloader und Session

//Assoziatives Array aller Turnieranmeldungen der Aktuellen Saison
$turnieranmeldungen = Turnier::get_all_anmeldungen();

$xml = new SimpleXMLElement('<turnieranmeldungen/>');

xml::array_to_xml($turnieranmeldungen,$xml,"meldungen","team");

Header('Content-type: text/xml');
print($xml->asXML());
