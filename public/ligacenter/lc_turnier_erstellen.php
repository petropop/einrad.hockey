<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../init.php';
require_once '../../logic/session_la.logic.php'; //Auth
require_once '../../logic/la_team_waehlen.logic.php';

$show_form = false;
if (isset($_GET['team_id'])){
    $ausrichter_team_id = (int) $_GET['team_id'];
    if (Team::is_ligateam($ausrichter_team_id)) {
        $show_form = true;
        $ausrichter_name = Team::id_to_name($ausrichter_team_id);
        $ausrichter_block = Tabelle::get_team_block($ausrichter_team_id);

        require_once '../../logic/turnier_erstellen.logic.php';

    } else {
        Html::error("Ungültige Team-ID");
        Helper::reload(); // Beendet dieses Skript
    }
}

/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
include '../../templates/header.tmp.php';?>

<h2> Turnier erstellen (Ligaausschuss) </h2>

<?php
include '../../templates/la_team_waehlen.tmp.php';

if ($show_form) {
    include '../../templates/turnier_erstellen.tmp.php';
}

include '../../templates/footer.tmp.php';