<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../init.php';

db::terminate();
db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');
$turnier_id = $_GET['turnier_id'];
$teams = Archiv::get_teams($turnier_id);
$spiele = Archiv::get_spiele($turnier_id);
$ergebnisse = Archiv::get_ergebnisse($turnier_id);
$turnierdetails = Archiv::get_turnierdetails($turnier_id);

/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
Html::$titel = 'Archiv | Deutschen Einradhockeyliga';
Html::$content = 'Hier kann man die Ergebnisse und Tabellen seit der ersten Saison im Jahr 1995 sehen.';
include '../../templates/header.tmp.php';
?>

<!-- Archiv -->
<h1 class="w3-text-primary">Ergebnis in <?=$turnierdetails['ort']?>, <?=strftime("%d.%m.%Y", strtotime($turnierdetails['datum']))?></h1>

<h2 class="w3-text-primary">Teams</h2>
<div class="w3-responsive w3-card">
    <table class="w3-table w3-striped">
        <thead class="w3-primary">
            <tr>
                <th><b>Teams</b></th>
            </tr>
        </thead>
    <?php foreach ($teams as $team) {?>
        <tr>
            <td><?=$team['teamname']?></td>
        </tr>
    <?php } ?>
    </table>
</div>

<h2 class="w3-text-primary">Spiele</h2>
<div class="w3-responsive w3-card">
    <table class="w3-table w3-striped">
        <thead class="w3-primary">
            <tr>
                <th><b>Spiel</b></th>
                <th><b>Team A</b></th>
                <th><b>Team B</b></th>
                <th colspan="3" class="w3-center"><b>Ergebnis</b></th>
                <th colspan="3" class="w3-center"><b>Penalty</b></th>
            </tr>
        </thead>
    <?php foreach ($spiele as $spiel) {?>
        <tr>
            <td><?=$spiel['spiel_id']?></td>
            <td><?=$spiel['team_a']?></td>
            <td><?=$spiel['team_b']?></td>
            <td class="w3-right-align"><?=$spiel['tore_a']?></td>
            <td class="w3-center">:</td>
            <td class="w3-right-left"><?=$spiel['tore_b']?></td>
            <td class="w3-right-align"><?=$spiel['penalty_a']?></td>
            <td class="w3-center">:</td>
            <td class="w3-right-left"><?=$spiel['penalty_b']?></td>
        </tr>
    <?php } ?>
    </table>
</div>

<h2 class="w3-text-primary">Turnierergebnis</h2>
<div class="w3-responsive w3-card">
    <table class="w3-table w3-striped">
        <thead class="w3-primary">
            <tr>
                <th><b>Platz</b></th>
                <th><b>Team</b></th>
                <th><b>Punkte</b></th>
            </tr>
        </thead>
    <?php foreach ($ergebnisse as $ergebnis) {?>
        <tr>
            <td><?=$ergebnis['platz']?></td>
            <td><?=$ergebnis['teamname']?></td>
            <td><?=$ergebnis['ergebnis']?></td>
        </tr>
    <?php } ?>
    </table>
</div>


<?php include '../../templates/footer.tmp.php';