<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../init.php';
require_once '../../logic/archiv.logic.php';

$uebersicht = Archiv::get_uebersicht();

Html::notice("Im Moment wird das Archiv vollständig überarbeitet. Die Informationen der Saisons 2020/21 sind in vollen Umfang vorhanden. Bei den restlichen Saisons bitten wir um Geduld. Das vorherige Archiv kann <a href='" . NAV::LINK_ARCHIV . "'>hier</a> eingesehen werden.", 'Überarbeitung', false);

/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
Html::$titel = 'Archiv | Deutschen Einradhockeyliga';
Html::$content = 'Hier kann man die Ergebnisse und Tabellen seit der ersten Saison im Jahr 1995 sehen.';
include '../../templates/header.tmp.php';
?>

<!-- Archiv -->
<h1 class="w3-text-primary">Archiv</h1>

<p>Die Deutsche Einradhockeyliga spielt seit 1995 den <i>Meister der Deutschen Einradhockeyliga</i> aus. In der ersten Saison wurde nach einem Modus Jeder-gegen-Jeden gespielt. Meister wurde das Team mit den meisten Punkten. Seit 1996 wird in einem Turniermodus gespielt und am Ende der Saison in einem Finalturnier der Meister der Deutschen Einradhockeyliga ermittelt.</p>

<div class="w3-responsive w3-card w3-section">
    <table class="w3-table w3-striped">
        <thead class="w3-primary">
            <tr>
                <th><b>Saison</b></th>
                <th><b>Teams</b></th>
                <th><b>Turniere</b></th>
                <th><b>Meister der Deutschen Einradhockeyliga</b></th>
            </tr>
        </thead>
    <?php foreach ($uebersicht as $saison) {?>
        <tr>
            <td><?=Html::link('archiv_saison.php?saison='. $saison['saison'], $saison['saisonname'], false)?></td>
            <td><?=$saison['teams_anzahl']?></td>
            <td><?=$saison['turnier_anzahl']?></td>
            <td><?=$saison['meister'] ?? 'Kein Meister ermittelt'?></td>
        </tr>
    <?php } ?>
    </table>
</div>

<!-- <iframe src="Nav::LINK_ARCHIV" style="width: 100%; height: 800px;" class="archiv w3-border-0" title="Archiv der Deutschen Einradhockeyliga"></iframe> -->

<?php include '../../templates/footer.tmp.php';