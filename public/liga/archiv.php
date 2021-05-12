<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../init.php';

$uebersicht = Archiv::get_uebersicht();

/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
Html::$titel = 'Archiv | Deutschen Einradhockeyliga';
Html::$content = 'Hier kann man die Ergebnisse und Tabellen seit der ersten Saison im Jahr 1995 sehen.';
include '../../templates/header.tmp.php';
?>

<!-- Archiv -->
<h1 class="w3-text-primary">Archiv</h1>
<!-- iframes sind ein sonderfall, html5 depreciated -->

<table class="w3-table w3-striped">
    <thead class="w3-primary">
        <tr>
            <th><b>Saison</b></th>
            <th><b>Turniere</b></th>
            <th><b>Teams</b></th>
            <th><b>Meister der Deutschen Einradhockeyliga</b></th>
        </tr>
    </thead>
<?php foreach ($uebersicht as $saison) {?>
    <tr>
        <td><?=$saison['saison'] + 1995?></td>
        <td><?=$saison['turnier_anzahl']?></td>
        <td><?=$saison['teams_anzahl']?></td>
        <td><?=$saison['meister'] ?? 'Kein Meister ermittelt'?></td>
    </tr>
<?php } ?>
</table>

<iframe src="<?= Nav::LINK_ARCHIV ?>" style="width:100%;height:800px;" class="archiv w3-border-0" title="Archiv der Deutschen Einradhockeyliga"></iframe>

<?php include '../../templates/footer.tmp.php';