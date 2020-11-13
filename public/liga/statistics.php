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