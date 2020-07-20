<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../logic/first.logic.php'; //autoloader und Session
require_once '../../logic/la_session.logic.php'; //Auth

//Formularauswertung
if(isset($_POST['change'])) {
    $passwort_alt = $_POST['passwort_alt'];
    $passwort_neu = $_POST['passwort_neu'];
    if (strlen($passwort_neu) >= 8 && strlen($passwort_neu) < 100){
        if(password_verify($passwort_alt, ligaleitung::get_la_password($_SESSION['la_id']))) {
            ligaleitung::set_la_password($_SESSION['la_id'],$passwort_neu);
            Form::affirm("Dein Passwort wurde geändert");
            header('Location: lc_start.php');
            die();
        }else{
            Form::error("Falsches Passwort");
        }
    }else{
        Form::error("Das Passwort muss mindestens acht Zeichen lang sein.");
    }    
}

Form::attention("Dein Passwort wird verschlüsselt gespeichert");

/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
$page_width = "500px";
include '../../templates/header.tmp.php';
?>

<form method="post" class="w3-panel w3-card-4">
    <h3> Ligacenter-Passwort ändern </h3>
    <label class="w3-text-primary" for="passwort_alt">Altes Passwort:</label>
    <input required class="w3-input w3-border w3-border-primary" type="password" id="passwort_alt" name="passwort_alt">
    <p>
    <label class="w3-text-primary" for="passwort_neu">Neues Passwort:</label>
    <input required class="w3-input w3-border w3-border-primary" type="password" id="passwort_neu" name="passwort_neu">
    <p>
    <input class="w3-button w3-tertiary" type="submit" name="change" value="Passwort ändern">
    </p>
</form>

<?php include '../../templates/footer.tmp.php';