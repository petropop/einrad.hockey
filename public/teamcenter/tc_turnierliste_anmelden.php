<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../logic/first.logic.php'; //autoloader und Session
require_once '../../logic/team_session.logic.php'; //Auth

$heute = date("Y-m-d", Config::time_offset());
$anmeldungen = Turnier::get_all_anmeldungen();
$akt_team = new Team($_SESSION['team_id']);
$turnier_angemeldet = $akt_team->get_turniere_angemeldet();
$anz_freilose = $akt_team->get_freilose();
$turniere = Turnier::get_all_turniere("WHERE turniere_liga.datum > '$heute'"); //wird dem Template übergeben

//Hinweis Live-Spieltag
$akt_spieltag = Tabelle::get_aktuellen_spieltag();
if (Tabelle::check_spieltag_live($akt_spieltag)){
    Form::attention(
        "Für den aktuelle Spieltag (ein Spieltag ist immer ein ganzes Wochenende) wurden noch nicht alle Ergebnisse eingetragen. Für die Turnieranmeldung gilt immer der Teamblock des letzten vollständigen Spieltages: "
        . Form::link("../liga/tabelle.php?spieltag=" . ($akt_spieltag - 1) . "#rang", "Spieltag " . ($akt_spieltag - 1)));
}

//Füge Links zum Weiterverarbeiten der ausgewählten Turniere hinzu
//diese werden dem Teamplate übergeben
foreach ($turniere as $key => $turnier){
    $turniere[$key]['link_anmelden'] = "tc_team_anmelden.php?turnier_id=". $turnier['turnier_id'];
    $turniere[$key]['link_details'] = "../liga/turnier_details.php?turnier_id=". $turnier['turnier_id'];
    if ($turnier['plaetze'] > count($anmeldungen[$turnier['turnier_id']]['spiele'] ?? array())){
        $turniere[$key]['freivoll'] = '<span class="w3-text-green">frei</span>';
    }else{
        $turniere[$key]['freivoll'] = '<span class="w3-text-red">voll</span>';
    }
    $turniere[$key]['block_color'] = 'w3-text-red';
    $freilos = true;
    if (Turnier::check_team_block_static($_SESSION['teamblock'],$turnier['tblock'])){
        $turniere[$key]['block_color'] = 'w3-text-green';
        $freilos = false;
    }
    if ($freilos && Turnier::check_team_block_freilos_static($_SESSION['teamblock'],$turnier['tblock']) && $anz_freilose>0){
        $turniere[$key]['block_color'] = 'w3-text-yellow';
    }
    $turniere[$key]['row_color'] = '';
    if (isset($turnier_angemeldet[$turnier['turnier_id']])){
        $liste = $turnier_angemeldet[$turnier['turnier_id']];
        if ($liste == 'spiele'){
            $turniere[$key]['row_color'] = 'w3-pale-green';
        }
        if ($liste == 'melde'){
            $turniere[$key]['row_color'] = 'w3-pale-yellow';
        }
        if ($liste == 'warte'){
            $turniere[$key]['row_color'] = 'w3-pale-blue';
        }
    }
}

/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
include '../../templates/header.tmp.php';?>

<h2 class="w3-text-primary" style='display: inline;'>Turnieranmeldung und -abmeldung</h2>
<!-- Trigger/Open the Modal -->
<button onclick="document.getElementById('id01').style.display='block'"
class="w3-button w3-text-blue w3-right" style='display: inline;'>Legende</button>

<!-- The Modal -->
<div id="id01" class="w3-modal">
  <div class="w3-modal-content" style="max-width:400px">
    <div class="w3-container w3-card-4 w3-border w3-border-black">
      <span onclick="document.getElementById('id01').style.display='none'"
      class="w3-button w3-display-topright">&times;</span>
        
        <h3>Legende:</h3>
        <p>
        Reihen:<br>
        <span class="w3-pale-green">Auf Spielen-Liste<br></span>
        <span class="w3-pale-blue">Auf Warteliste<br></span>
        <span class="w3-pale-yellow">Auf Meldeliste<br></span>
        <br>
        <i><span class="w3-text-green">(Block)</span>: Anmeldung möglich</i><br>
        <i><span class="w3-text-yellow">(Block)</span>: Freilos möglich</i><br>
        <i><span class="w3-text-red">(Block)</span>: Falscher Block</i><br>
        <br>
        <i><span class="w3-text-green">frei</span>: Plaetze auf der Spielen-Liste frei</i><br>
        <i><span class="w3-text-red">voll</span>: Spielen-Liste ist voll</i><br>
        </p>
    </div>
  </div>
</div>


<script>
// Get the modal
var modal = document.getElementById('id01');

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
<?php include '../../templates/turnierliste.tmp.php';?>

<?php include '../../templates/footer.tmp.php';