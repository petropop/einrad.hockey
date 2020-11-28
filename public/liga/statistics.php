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
            <p class="w3-center w3-xlarge"><i><?=$hoechster_sieg["team_a"]?></i> - <i><?=$hoechster_sieg["team_b"]?></i><br><?=$hoechster_sieg["tore_a"]?> : <?=$hoechster_sieg["tore_b"]?></p>
            <p class="w3-center w3-large">am</p>
            <p class="w3-center w3-xlarge"><?=$hoechster_sieg["datum"]?></p>
            </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-secondary">
            <p class="w3-center w3-large">Torärmstes Spiel saisonübergreifend</p>
            <p class="w3-center w3-xlarge"><i><?=$spiel_wenigste_tore["team_a"]?></i> - <i><?=$spiel_wenigste_tore["team_b"]?></i><br><?=$spiel_wenigste_tore["tore_a"]?> : <?=$spiel_wenigste_tore["tore_b"]?></p>
            <p class="w3-center w3-large">am</p>
            <p class="w3-center w3-xlarge"><?=$spiel_wenigste_tore["datum"]?></p>
            </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-tertiary">
            <p class="w3-center w3-large">Torreichstes Spiel saisonübergreifend</p>
            <p class="w3-center w3-xlarge"><i><?=$spiel_meiste_tore["team_a"]?></i> - <i><?=$spiel_meiste_tore["team_b"]?></i><br><?=$spiel_meiste_tore["tore_a"]?> : <?=$spiel_meiste_tore["tore_b"]?></p>
            <p class="w3-center w3-large">am</p>
            <p class="w3-center w3-xlarge"><?=$spiel_meiste_tore["datum"]?></p>
        </div>
    </div>
</div>
<div class="w3-row">  
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-tertiary">
            <p class="w3-center w3-large">Torreichstes unentschieden saisonübergreifend</p>
            <p class="w3-center w3-xlarge"><i><?=$torreichstes_unentschieden["team_a"]?></i> - <i><?=$torreichstes_unentschieden["team_b"]?></i><br><?=$torreichstes_unentschieden["tore_a"]?> : <?=$torreichstes_unentschieden["tore_b"]?></p>
            <p class="w3-center w3-large">am</p>
            <p class="w3-center w3-xlarge"><?=$torreichstes_unentschieden["datum"]?></p>       
        </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-primary">
            <p class="w3-center w3-large">Torärmstes unentschieden saisonübergreifend</p>
            <p class="w3-center w3-xlarge"><i><?=$toraermstes_unentschieden["team_a"]?></i> - <i><?=$toraermstes_unentschieden["team_b"]?></i><br><?=$toraermstes_unentschieden["tore_a"]?> : <?=$toraermstes_unentschieden["tore_b"]?></p>
            <p class="w3-center w3-large">am</p>
            <p class="w3-center w3-xlarge"><?=$toraermstes_unentschieden["datum"]?></p>       
        </div>
    </div>
</div>
<div class="w3-row">  
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-secondary">
            <p class="w3-center w3-large">Einzelsiege in Folge saisonübergreifend</p>
            <p class="w3-center w3-xlarge"><i><?=$seriensieger["team_name"]?></i></p>
            <p class="w3-center w3-xlarge"><?=$seriensieger["max_siege"]?> Siege</p>
            <p class="w3-center w3-large">letzter Sieg am</p>
            <p class="w3-center w3-xlarge"><?=$seriensieger["datum"]?></p>
        </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-tertiary">
            <p class="w3-center w3-large">Turniersiege in Folge saisonübergreifend</p>
            <p class="w3-center w3-xlarge"><i><?=$seriensieger_turnier["team_name"]?></i><br><?=$seriensieger_turnier["max_siege"]?> Turniersiege</p>
            <p class="w3-center w3-large">letzter Turniersieg am</p>
            <p class="w3-center w3-xlarge"><?=$seriensieger_turnier["datum"]?></p>
        </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-primary">
            <p class="w3-center w3-large">Turniersiege saisonübergreifend</p>
            <p class="w3-center w3-xlarge"><i><?=$turniersiege["team_name"]?></i><br><?=$turniersiege["siege"]?> Turniersiege</p>
        </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-tertiary">
            <p class="w3-center w3-large">Turnierteilnahmen saisonübergreifend</p>
            <p class="w3-center w3-xlarge"><i><?=$turnierteilnahmen["team_name"]?></i><br><?=$turnierteilnahmen["teilnahmen"]?> Turniere</p>
        </div>
    </div>
</div>
<div class="w3-row"> 
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-tertiary">
            <p class="w3-center w3-large">Maximale Entfernung zwischen aktiven Ligateams</p>
            <p class="w3-center w3-xlarge"><?=$max_entf_team["entfernung"]?> km</p>
            <p class="w3-center w3-large">von <i><?=$max_entf_team["ort_a"]?></i> nach <i><?=$max_entf_team["ort_b"]?></i></p>
        </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-primary">
            <p class="w3-center w3-large">Maximale Anreise<sup>*</sup></p>
            <p class="w3-center w3-xlarge"><?=$max_anreise["entfernung"]?> km</p>
            <p class="w3-center w3-large"><i><?=$max_anreise["teamname"]?></i><br> von <br><i><?=$max_anreise["ort"]?></i> nach <i><?=$max_anreise["turnier_ort"]?></i></p>
            <p class="w3-center w3-tiny">*Luftlinie</p>
        </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-tertiary">
            <p class="w3-center w3-large">Maximale Anreise aller Teams zum Turnier <sup>*</sup></p>
            <p class="w3-center w3-xlarge"><?=$turnier_max_anreise["sum_entfernung"]?> km</p>
            <p class="w3-center w3-large">Zum Turnier in</p>
            <p class="w3-center w3-large"><i><?=$turnier_max_anreise["turnier_ort"]?></i></p>
            <p class="w3-center w3-large">am</p>
            <p class="w3-center w3-large"><?=$turnier_max_anreise["datum"]?></p>
            <p class="w3-center w3-tiny">*Luftlinie</p>
        </div>
    </div>
    <div class="w3-container w3-third">
        <div class="w3-panel w3-card-4 w3-primary">
            <p class="w3-center w3-large">Minimale Anreise aller Teams zum Turnier <sup>*</sup></p>
            <p class="w3-center w3-xlarge"><?=$turnier_min_anreise["sum_entfernung"]?> km</p>
            <p class="w3-center w3-large">Zum Turnier in</p>
            <p class="w3-center w3-large"><i><?=$turnier_min_anreise["turnier_ort"]?></i></p>
            <p class="w3-center w3-large">am</p>
            <p class="w3-center w3-large"><?=$turnier_min_anreise["datum"]?></p>
            <p class="w3-center w3-tiny">*Luftlinie</p>
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