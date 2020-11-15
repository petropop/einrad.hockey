<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../logic/first.logic.php'; //autoloader und Session
require_once '../../logic/statistics.logic.php'; 
//Variablen

//Formularauswertung

//Messages

/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
include '../../templates/header.tmp.php';
?>

<div class="w3-row">
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-primary">
            <p class="w3-center w3-large">Turniere</p>
            <p class="w3-center w3-xxxlarge"><?=$turniere?></p>
        </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-secondary">
            <p class="w3-center w3-large">Spiele</p>
            <p class="w3-center w3-xxxlarge"><?=$spiele?></p>
        </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-tertiary">
            <p class="w3-center w3-large">Punkte</p>
            <p class="w3-center w3-xxxlarge"><?=$punkte?></p>
        </div>
    </div>
</div>

<div class="w3-row">
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-tertiary">
            <p class="w3-center w3-large">Gesamtore</p>
            <p class="w3-center w3-xxxlarge"><?=$gesamt_tore?></p>
        </div>
    </div>
    <div class="w3-container w3-twothird">
        <div class="w3-panel w3-card-4 w3-primary">
            <p class="w3-center w3-large">Spielzeit</p>
            <p class="w3-center w3-xxxlarge">~<?=$spielzeit?></p>
        </div>
    </div>
</div>

<div class="w3-row">
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-secondary">
            <p class="w3-center w3-large">Penalty</p>
            <p class="w3-center w3-xxxlarge"><?=$penalty?></p>
        </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-secondary">
            <p class="w3-center w3-large">Tore</p>
            <p class="w3-center w3-xxxlarge"><?=$tore?></p>
        </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-tertiary">
            <p class="w3-center w3-large">Gegentore</p>
            <p class="w3-center w3-xxxlarge"><?=$gegentore?></p>
        </div>
    </div>
</div>
<div class="w3-row">  
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-primary">
            <p class="w3-center w3-large">Höchster Sieg saisonübergreifend</p>
            <p class="w3-center w3-xlarge"><?=$hoechster_sieg["team_a"]?> - <?=$hoechster_sieg["team_b"]?><br><?=$hoechster_sieg["tore_a"]?> - <?=$hoechster_sieg["tore_b"]?></p>
            </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-secondary">
            <p class="w3-center w3-large">Torärmstes Spiel saisonübergreifend</p>
            <p class="w3-center w3-xlarge"><?=$spiel_wenigste_tore["team_a"]?> - <?=$spiel_wenigste_tore["team_b"]?><br><?=$spiel_wenigste_tore["tore_a"]?> - <?=$spiel_wenigste_tore["tore_b"]?></p>
            </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-tertiary">
            <p class="w3-center w3-large">Torreichstes Spiel saisonübergreifend</p>
            <p class="w3-center w3-xlarge"><?=$spiel_meiste_tore["team_a"]?> - <?=$spiel_meiste_tore["team_b"]?><br><?=$spiel_meiste_tore["tore_a"]?> - <?=$spiel_meiste_tore["tore_b"]?></p>
            </div>
    </div>
</div>
<div class="w3-row">  
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-tertiary">
            <p class="w3-center w3-large">Torreichstes unentschieden saisonübergreifend</p>
            <p class="w3-center w3-xlarge"><?=$torreichstes_unentschieden["team_a"]?> - <?=$torreichstes_unentschieden["team_b"]?><br><?=$torreichstes_unentschieden["tore_a"]?> - <?=$torreichstes_unentschieden["tore_b"]?></p>
            </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-primary">
            <p class="w3-center w3-large">Torärmstes unentschieden saisonübergreifend</p>
            <p class="w3-center w3-xlarge"><?=$toraermstes_unentschieden["team_a"]?> - <?=$toraermstes_unentschieden["team_b"]?><br><?=$toraermstes_unentschieden["tore_a"]?> - <?=$toraermstes_unentschieden["tore_b"]?></p>
            </div>
    </div>
</div>
<div class="w3-row">  
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-secondary">
            <p class="w3-center w3-large">Einzelsiege in Folge saisonübergreifend</p>
            <p class="w3-center w3-xlarge"><?=$seriensieger["team_name"]?><br><?=$seriensieger["max_siege"]?> Siege</p>
            </div>
    </div>
</div>


<div class="w3-row">
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-secondary">
            <p class="w3-center w3-large">Spielerinnen</p>
            <p class="w3-center w3-xxxlarge"><?=$spielerinnen?></p>
        </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-secondary">
            <p class="w3-center w3-large">Spieler</p>
            <p class="w3-center w3-xxxlarge"><?=$spieler?></p>
        </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-secondary">
            <p class="w3-center w3-large">Kader</p>
            <p class="w3-center w3-xxxlarge"><?=$kader?></p>
        </div>
    </div>
</div>

<div class="w3-row">
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-secondary">
            <p class="w3-center w3-large">Schiedsrichter*innen</p>
            <p class="w3-center w3-xxxlarge"><?=$schiedsrichter?></p>
        </div>
    </div>
</div>

<?php include '../../templates/footer.tmp.php';