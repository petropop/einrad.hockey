<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../init.php';

db::terminate();
db::initialize(Env::HOST_NAME, Env::USER_NAME, Env::PASSWORD, 'db_einradhockey_archiv');
$saison = (isset($_GET['saison'])) ? (int)$_GET['saison'] : Config::SAISON;
$turniere = Archiv::get_turniere($saison);

/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
Html::$titel = 'Archiv | Deutschen Einradhockeyliga';
Html::$content = 'Hier kann man die Ergebnisse und Tabellen seit der ersten Saison im Jahr 1995 sehen.';
include '../../templates/header.tmp.php';
?>

<!-- Archiv -->
<h1 class="w3-text-primary">Archiv, Saison <?=$saison?></h1>

<div class="w3-responsive w3-card">
    <table class="w3-table w3-striped">
        <thead class="w3-primary">
            <tr>
                <th><b>Datum</b></th>
                <th><b>Ort</b></th>
                <th><b>Art</b></th>
                <th><b>Block</b></th>
            </tr>
        </thead>
    <?php foreach ($turniere as $turnier) {?>
        <tr>
            <td><?=strftime("%a", strtotime($turnier['datum']))?>, <?=strftime("%d.%m.", strtotime($turnier['datum']))?></a></td>
            <td><?=$turnier['turnier_id']?></td>
            <td><?=$turnier['art'] == 'final' ? '--' : $turnier['art']?></td>
            <td><?=$turnier['tblock'] == 'final' ? 'FINALE' : $turnier['tblock'] ?></td>
        </tr>
    <?php } ?>
    </table>
</div>

<?php include '../../templates/footer.tmp.php';