<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../logic/first.logic.php'; //autoloader und Session
require_once '../../logic/la_session.logic.php'; //Auth

//Formularauswertung
require_once '../../logic/neuigkeit_eintragen.logic.php';

Form::attention("Die Verwendung von Html-Tags ist als Ligaausschuss standardmäßig aktiviert.");

/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
include '../../templates/header.tmp.php';
include '../../templates/neuigkeit_eintragen.tmp.php';
include '../../templates/footer.tmp.php';