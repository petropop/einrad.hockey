<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../logic/first.logic.php'; //autoloader und Session

$daten=Turnier::get_all_turniere("WHERE turniere_liga.phase != 'ergebnis' AND SAISON='".Config::SAISON."'");

if (empty($daten)){
    Form::affirm("Es wurden noch keine Turniere eingetragen.");
}

$all_anmeldungen=Turnier::get_all_anmeldungen();

//Turnierdarten parsen
foreach ($daten as $turnier_id => $turnier){
    $daten[$turnier_id]['wochentag'] = strftime("%A", strtotime($daten[$turnier_id]['datum']));
    $daten[$turnier_id]['datum'] = strftime("%d.%m.", strtotime($daten[$turnier_id]['datum']));
    $daten[$turnier_id]['startzeit'] = substr($daten[$turnier_id]['startzeit'], 0, -3);

    if ($daten[$turnier_id]['art'] == 'spass'){
        $daten[$turnier_id]['tblock'] = 'Spaß';
    }
    if ($daten[$turnier_id]['besprechung'] == 'Ja'){
        $daten[$turnier_id]['besprechung'] = 'Gemeinsame Teambesprechung 15&nbsp;min vor Turnierbeginn';
    }else{
        $daten[$turnier_id]['besprechung'] = '';
    }
    //Spielmodus
    if ($daten[$turnier_id]['spielplan'] == 'jgj'){
        $daten[$turnier_id]['spielplan'] = 'Jeder-gegen-Jeden';
    }elseif($daten[$turnier_id]['spielplan'] == 'dko'){
        $daten[$turnier_id]['spielplan'] = 'Doppel-KO';
    }elseif($daten[$turnier_id]['spielplan'] == 'gruppen'){
        $daten[$turnier_id]['spielplan'] = 'zwei Gruppen';
    }
}

//Parsen der Warteliste und Spieleliste
$warteliste = $spieleliste = $meldeliste = array();
$anz_warteliste = $anz_spieleliste = $anz_meldeliste = array();
foreach ($all_anmeldungen as $turnier_id => $liste){

    $anz_warteliste[$turnier_id] = count($liste['warte'] ?? array());
    $anz_spieleliste[$turnier_id] = count($liste['spiele'] ?? array());
    $anz_meldeliste[$turnier_id] = count($liste['melde'] ?? array());
    $freie_plaetze = $daten[$turnier_id]['plaetze'] - $anz_spieleliste[$turnier_id] - $anz_meldeliste[$turnier_id] - $anz_warteliste[$turnier_id];

    //Oben rechts Plätze frei
    if ($freie_plaetze > 0 && $daten[$turnier_id]['phase'] != 'spielplan'){
        $daten[$turnier_id]['plaetze_frei'] = '<span class="w3-text-green">frei</span>';
    }elseif ($freie_plaetze < 0 && $daten[$turnier_id]['phase'] == 'offen'){
        $daten[$turnier_id]['plaetze_frei'] = '<span class="w3-text-yellow">losen</span>';
    }elseif (($daten[$turnier_id]['plaetze'] - $anz_spieleliste[$turnier_id]) <= 0){
        $daten[$turnier_id]['plaetze_frei'] = '<span class="w3-text-red">voll</span>';
    }else{
        $daten[$turnier_id]['plaetze_frei'] = '<span class="w3-text-blue">Spielplan</span>';
    }
}

/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
$titel = "Turnierliste | Deutsche Einradhockeyliga";
$page_width = "800px";
$content = "Eine Liste aller ausstehenden Spaß-, Final- und Ligaturniere der Deutschen Einradhockeyliga.";
include '../../templates/header.tmp.php';
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

<script>
//Turnierergebnisse filtern
$(document).ready(function(){
  $("#myInput").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#myDIV section").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});

//Turnierinfos ausklappen
function modal(turnier_id){
    var x = document.getElementById(turnier_id);
    if (window.getComputedStyle(x).display === "none") {
        x.style.display = "block";
    }else{
        if (window.getComputedStyle(x).display === "block") {
            x.style.display = "none";
        }
    }
}
</script>

<h1 class="w3-text-primary">Ausstehende Turniere</h1>

<!-- Suchfeld fürs Filtern der Turniere -->
<input id="myInput" style='max-width: 280px;' class='w3-input w3-border w3-border-grey' type="text" placeholder="Suche..">

<div id="myDIV"><!-- zu durchsuchendes div -->
    <!--Turnierpanels -->
    <?php foreach ($daten as $turnier){?>
        <section onclick="modal('modal<?=$turnier['turnier_id']?>')"
            class='w3-display-container w3-panel <?php if ($turnier['art']=='final'){?>w3-palered<?php }?> w3-card'
            style='cursor: pointer'
            id='<?=$turnier['turnier_id']?>'>
            <!-- Angezeigtes Turnierpanel -->
            <div class='w3-panel'>
                <div class="w3-center">
                    <h4 class=''><?=$turnier['datum']?> <span class="w3-text-primary"><?=$turnier['ort']?></span> (<?=$turnier['tblock']?>)</h4> 
                    <p class='w3-text-grey'><?=$turnier['tname']?></p>
                </div>
                <div class="w3-small w3-text-grey">
                    <i class='w3-display-topleft w3-padding'><?=$turnier['plaetze_frei'] ?? '<span class="w3-text-green">frei</span>'?></i>
                    <i class='w3-display-bottomleft w3-padding'><?=$turnier['phase']?></i>
                    <i class='w3-display-topright w3-padding'><?=($anz_spieleliste[$turnier['turnier_id']] ?? 0) ."(". (($anz_warteliste[$turnier['turnier_id']] ?? 0)+($anz_meldeliste[$turnier['turnier_id']] ?? 0)) .")"?> von <?=$turnier['plaetze']?></i>
                    <i class='w3-display-bottomright w3-padding'><?=$turnier['teamname']?></i>
                </div>

                <!-- Ausklappbarer Content -->
                <?php if ($turnier['art'] != 'spass'){?>
                    <div style='display: none' class='' id="modal<?=$turnier['turnier_id']?>">
                        <!-- Listen -->
                        <p class="w3-text-grey w3-border-bottom w3-border-grey">Listen</p>                       
                        <div class='w3-row'>
                            <div class='w3-third'>
                                <h4 class='w3-text-primary'><span>Spielen-Liste:</span></h4>
                                <?php if(!empty($all_anmeldungen[$turnier['turnier_id']]['spiele'])){?>
                                <!-- Ausklappbarer Content -->
                                    <p><i>
                                        <?php foreach ($all_anmeldungen[$turnier['turnier_id']]['spiele'] as $team){?>
                                            <?=$team['teamname']?><span class="w3-text-primary"> (<?=$team['tblock'] ?: 'NL'?>)</span><br>
                                        <?php }//end foreach?>
                                    </i></p>
                                <?php }else{?> <i>leer</i> <?php }//end if?>
                            </div>
                            <div class='w3-third'>
                                <?php if(!empty($all_anmeldungen[$turnier['turnier_id']]['melde'])){?>
                                    <h4 class='w3-text-primary'><span>Meldeliste:</span></h4>
                                    <p><i>
                                        <?php foreach (($all_anmeldungen[$turnier['turnier_id']]['melde']) as $team){?>
                                            <?=$team['teamname']?> <span class="w3-text-primary">(<?=$team['tblock'] ?? 'NL'?>)</span><br>
                                        <?php }//end foreach?>
                                    </i></p>
                                <?php }//end if?>
                            </div>
                            <div class='w3-third'>
                                <?php if(!empty($all_anmeldungen[$turnier['turnier_id']]['warte'])){?>
                                    <h4 class='w3-text-primary'><span>Warteliste:</span></h4>
                                    <p><i>
                                        <?php foreach (($all_anmeldungen[$turnier['turnier_id']]['warte']) as $team){?>
                                            <?=$team['position_warteliste'] . ". " . $team['teamname']?> <span class="w3-text-primary">(<?=$team['tblock'] ?? 'NL'?>)</span><br>
                                        <?php }//end foreach?>
                                    </i></p>
                                <?php }//end if?>
                            </div>
                        </div>
                    <?php } //end if spass?>
                    <!-- Turnierdetails -->
                    <p class="w3-text-grey w3-border-bottom w3-border-grey">Details</p>
                    <div class="w3-responsive">
                        <table class="w3-table">
                            <tr>
                                <td class="" style="width: 100px">Tag</td>
                                <td><?=$turnier['wochentag']?></td>
                            </tr>
                            <tr>
                                <td class="" style="width: 100px">Plätze</td>
                                <td><?=$turnier['plaetze']?> (<?=$turnier['spielplan']?>)</td>
                            </tr>
                            <tr>
                                <td class="">Startzeit</td>
                                <td><?=$turnier['startzeit']?>&nbsp;Uhr<?php if (!empty($turnier['besprechung'])){?> <i>(<?=$turnier['besprechung']?>)</i><?php } //endif?></td>
                            </tr>
                            <tr>
                                <td style="vertical-align: middle" class="">Hinweis</td>
                                <td><?=$turnier['hinweis']?></td>
                            </tr>
                            <?php if($turnier['phase'] == 'spielplan'){?>
                            <tr>
                                <td>Spielplan</td>
                                <td>
                                <?php if(!empty($turnier['link_spielplan'])){?>
                                    <a href="<?=$turnier['link_spielplan']?>" class="no w3-text-blue w3-hover-text-secondary">Download Spielplan</a>
                                <?php }//end if?>
                                </td>
                            </tr>
                                <?php }//end if?>
                        </table>
                    </div>          
                        
                    <!-- Links -->
                    <div style="margin-bottom: 24px;">
                        <p class="w3-text-grey w3-border-bottom w3-border-grey">Links</p>
                        <p><a class="no w3-text-blue w3-hover-text-secondary" href='../liga/turnier_details.php?turnier_id=<?=$turnier['turnier_id']?>'><i class="material-icons">info</i> Alle Turnierdetails</a></p>
                        <?php if (isset($_SESSION['la_id'])){?> 
                            <p><a class="no w3-text-blue w3-hover-text-secondary" href='../ligacenter/lc_turnier_bearbeiten.php?turnier_id=<?=$turnier['turnier_id']?>'>Turnier bearbeiten (Ligaausschuss)</a></p>
                            <p><a class="no w3-text-blue w3-hover-text-secondary" href='../ligacenter/lc_team_anmelden.php?turnier_id=<?=$turnier['turnier_id']?>'>Teams anmelden (Ligaausschuss)</a></p>
                            <p><a class="no w3-text-blue w3-hover-text-secondary" href='../ligacenter/lc_turnier_log.php?turnier_id=<?=$turnier['turnier_id']?>'>Turnierlog einsehen (Ligaausschuss)</a></p>
                        <?php } //endif?>

                        <?php if (isset($_SESSION['team_id'])){?>
                            <a class="no w3-text-blue w3-hover-text-secondary" href='../teamcenter/tc_team_anmelden.php?turnier_id=<?=$turnier['turnier_id']?>'><i class="material-icons">how_to_reg</i> Zur Anmeldeseite</a></p>
                        <?php } //endif?>

                        <?php if (($_SESSION['team_id'] ?? '') == $turnier['ausrichter']){?>
                            <a class="no w3-text-blue w3-hover-text-secondary" href='../teamcenter/tc_turnier_bearbeiten.php?turnier_id=<?=$turnier['turnier_id']?>'><i class="material-icons">create</i> Turnier als Ausrichter bearbeiten</a></p>
                        <?php } //endif?>
                    </div>
                </div>
            </div>
        </section>
    <?php } //end foreach?>
</div>
<?php include '../../templates/footer.tmp.php';







