<?php
if(isset($_POST['la_team_waehlen'])){
    $teamname = $_POST['la_team_waehlen'];
    $team_id  = Team::teamname_to_teamid($teamname);
    if (Team::is_ligateam($team_id)){
        header("Location: " . db::escape($_SERVER['PHP_SELF']) . "?team_id=$team_id");
        die();
    }else{
        Form::error ("Team wurde nicht gefunden oder ist kein aktives Ligateam.");
        header("Location: " . db::escape($_SERVER['PHP_SELF']));
        die();
    }
}