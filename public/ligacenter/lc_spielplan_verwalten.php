<?php
/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LOGIK////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
require_once '../../init.php';
require_once '../../logic/session_la.logic.php'; //Auth

//Turnierklasse erstellen
$turnier_id = (int)@$_GET['turnier_id'];
$turnier = new Turnier($turnier_id);

//Existiert das Turnier?
if (empty($turnier->details)) {
    Helper::not_found("Turnier konnte nicht gefunden werden.");
}

//Vorhandenes Ergebnis anzeigen
$teamliste = $turnier->get_liste_spielplan();
$anzahl_teams = count($teamliste);
$turnier_ergebnis = $turnier->get_ergebnis();
//Ergebnis löschen
if (isset($_POST['ergebnis_loeschen'])) {
    $turnier->delete_ergebnis();
    $turnier->set_liga('phase', 'spielplan');
    Html::info("Ergebnis wurde gelöscht. Das Turnier wurde in die Spielplanphase versetzt.");
    header("Location: lc_spielplan_verwalten.php?turnier_id=" . $turnier->details['turnier_id']);
    die();
}

//Ergebnis eintragen
if (isset($_POST['ergebnis_eintragen'])) {
    if (!Tabelle::check_ergebnis_eintragbar($turnier)) {
        Html::error("Turnierergebnis wurde nicht eingetragen");
        $error = true;
    }
    if (count(array_unique($_POST['team_id'])) != count($_POST['team_id'])) {
        Html::error("Es wurden Teams doppelt eingetragen!");
        $error = true;
    }
    for ($platz = 1; $platz <= $anzahl_teams; $platz++) {
        if (empty($_POST['team_id'][$platz]) || empty($_POST['ergebnis'][$platz])) {
            $error = true;
            Html::error("Formular wurde unvollständig übermittelt");
            break;
        }
    }
    if ($error ?? false) {
        header("Location: lc_spielplan_verwalten.php?turnier_id=" . $turnier->id);
        die();
    }
    // Kein Fehler
    $turnier->delete_ergebnis();
    for ($platz = 1; $platz <= $anzahl_teams; $platz++) {
        $turnier->set_ergebnis($_POST['team_id'][$platz], $_POST['ergebnis'][$platz], $platz);
    }
    $turnier->set_liga('phase', 'ergebnis');
    Html::info("Ergebnisse wurden manuell eingetragen. Das Turnier wurde in die Ergebnisphase versetzt.");
    header("Location: lc_spielplan_verwalten.php?turnier_id=" . $turnier->details['turnier_id']);
    die();
}

// Spielplan automatisch erstellen
if (isset($_POST['auto_spielplan_erstellen'])) {
    $error = false;
    if ($turnier->details['phase'] != "melde") {
        Html::error("Das Turnier muss in der Meldephase sein.");
        $error = true;
    }
    if ($anzahl_teams < 4 || $anzahl_teams > 8) {
        Html::error("Falsche Anzahl an Teams. Nur 4er - 8er Jeder-gegen-Jeden Spielpläne können erstellt werden.");
        $error = true;
    }
    if (!empty($turnier->details['spielplan_link'])) {
        Html::error("Spielplan konnte nicht erstellt werden. Es existiert ein manuell hochgeladener Spielplan.");
        $error = true;
    }
    if (!$error) {
        if (Spielplan::fill_vorlage($turnier)) {
            Html::info("Das Turnier wurde in die Spielplan-Phase versetzt. Der Spielplan wird jetzt angezeigt.");
            header('Location: ../liga/spielplan.php?turnier_id=' . $turnier->id);
            die();
        }

        Html::error("Spielplan konnte nicht erstellt werden.");
    }
}

//Spielplan löschen
if (isset($_POST['auto_spielplan_loeschen'])) {
    Spielplan::delete($turnier);
    Html::info("Der dynamisch erstellte Spielplan wurde gelöscht. Das Turnier wurde in die Meldephase versetzt!");
    header('Location:' . db::escape($_SERVER['REQUEST_URI']));
    die();
}

// Spielplan oder Ergebnis manuell hochladen
if (isset($_POST['spielplan_hochladen'])) {
    if (Spielplan::check_exist($turnier->id)) {
        $error = true;
        Html::error("Hochladen nicht möglich. Es existiert bereits ein dynamisch erstellter Spielplan.");
    }
    if (!empty($_FILES["spielplan_file"]["tmp_name"])) {
        $target_dir = "../uploads/s/spielplan/";
        // PDF wird hochgeladen, target_file_pdf = false, falls fehlgeschlagen.
        $target_file_pdf = Neuigkeit::upload_dokument($_FILES["spielplan_file"], $target_dir);
        if ($target_file_pdf !== false) {
            if ($_POST['sp_or_erg'] === 'ergebnis') {
                $turnier->upload_spielplan($target_file_pdf, 'ergebnis');
                Html::info("Manueller Spielplan hochgeladen. Das Turnier wurde in die Ergebnis-Phase versetzt.");
            } else {
                $turnier->upload_spielplan($target_file_pdf, 'spielplan');
                Html::info("Manueller Spielplan hochgeladen. Das Turnier wurde in die Spielplan-Phase versetzt.");
            }
            header("Location: lc_spielplan_verwalten.php?turnier_id=$turnier->id");
            die();
        }
        Html::error("Fehler beim Upload");

    } else {
        Html::error("Es wurde kein Spielplan gefunden");
    }
}

// Spielplan löschen
if (isset($_POST['spielplan_delete'])) {
    unlink($turnier->details['spielplan_datei']);
    $turnier->upload_spielplan('', 'melde');
    Html::info("Spielplan- / Ergebnisdatei wurde gelöscht. Turnier wurde in die Meldephase versetzt.");
    header("Location: lc_spielplan_verwalten.php?turnier_id=$turnier->id");
    die();
}

// Hinweis Finalturniere-Ergebnis
if ($turnier->details['art'] === 'final') {
    Html::notice("Beim Eintragen von Finalturnieren kann eine beliebige Punktzahl eingeben werden.");
}

/////////////////////////////////////////////////////////////////////////////
////////////////////////////////////LAYOUT///////////////////////////////////
/////////////////////////////////////////////////////////////////////////////

include '../../templates/header.tmp.php';
?>

    <!-- Überschrift -->
    <h1 class="w3-text-primary">
        <span class="w3-text-grey">Spielplan/Ergebnis</span>
        <br>
        <?= $turnier->details['datum'] ?> <?= $turnier->details['tname'] ?> <?= $turnier->details['ort'] ?>
        (<?= $turnier->details['tblock'] ?>)
        <br>
    </h1>

    <!-- Teamliste -->
    <h3>Spielen-Liste</h3>
    <div class="w3-responsive w3-card">
        <table class="w3-table w3-striped">
            <thead class="w3-primary">
            <tr>
                <th>Team ID</th>
                <th>Teamname</th>
                <th class="w3-center">Teamblock</th>
                <th class="w3-center">Wertung</th>
            </tr>
            </thead>
            <?php foreach ($teamliste as $team) { ?>
                <tr>
                    <td><?= $team['team_id'] ?></td>
                    <td><?= $team['teamname'] ?></td>
                    <td class="w3-center"><?= $team['tblock'] ?: 'NL' ?></td>
                    <td class="w3-center"><?= $team['wertigkeit'] ?: 'Siehe Modus' ?></td>
                </tr>
            <?php } //end foreach?>
        </table>
    </div>

    <!-- Dynamischer Spielplan erstellen -->
    <h2 class="w3-text-primary w3-bottombar">JgJ-Spielplan erstellen</h2>
<?php if (empty($turnier->details['spielplan_datei'])) { ?>
    <form method="post">
        <?php if (Spielplan::check_exist($turnier->id)) { ?>
            <p>
                <input type="submit"
                       name="auto_spielplan_loeschen"
                       value="JgJ-Spielplan löschen"
                       class="w3-button w3-secondary">
            </p>
        <?php } else { ?>
            <p>
                <input type="submit"
                       name="auto_spielplan_erstellen"
                       value="JgJ-Spielplan erstellen"
                       class="w3-button w3-tertiary">
            </p>
        <?php } // endif ?>
    </form>
<?php } else { ?>
    <p>Bitte zuerst den manuell hochgeladenen Spielplan löschen.</p>
<?php } // endif ?>

    <!-- Manuellen Spielplan hochladen -->
    <h2 class="w3-text-primary w3-bottombar">oder PDF- oder XLSX-Spielplan hochladen</h2>

    <form method="post" enctype="multipart/form-data">

        <?php if (Spielplan::check_exist($turnier->id)) { ?>
            <p>Bitte zuerst den dynamischen Spielplan löschen.</p>
        <?php } else { ?>

            <?php if (empty($turnier->details['spielplan_datei'])) { ?>
                <p class="w3-text-grey">Nur .pdf oder .xlsx Format</p>
                <p>
                    <input required type="file" name="spielplan_file" id="spielplan_file" class="w3-button w3-tertiary">
                </p>
                <p>
                    <label class="w3-text-grey" for="sp_or_erg">Spielplan oder Ergebnis?</label><br>
                    <select required
                            id="sp_or_erg"
                            name="sp_or_erg"
                            class="w3-select w3-border w3-border-primary"
                            style="max-width: 200px">
                        <option value="" selected disabled>Bitte wählen</option>
                        <option value="spielplan">Spielplan</option>
                        <option value="ergebnis">Ergebnis</option>
                    </select>
                </p>
                <p>
                    <input type="submit" name="spielplan_hochladen" value="Upload" class="w3-button w3-secondary">
                </p>
            <?php } //end if?>

        <?php } //end if?>

        <?php if (!empty($turnier->details['spielplan_datei'])) { ?>
            <p>
                <?= Html::link($turnier->details['spielplan_datei'], 'Spielplan/Ergebnis herunterladen', true); ?>
            </p>
            <p>
                <input type="submit"
                       name="spielplan_delete"
                       value="Vorhandene Spielplandatei löschen"
                       class="w3-button w3-secondary">
            </p>
        <?php } //end if?>

    </form>

    <!-- Ergebnisse eintragen -->
    <h2 class="w3-bottombar w3-text-primary">Ergebnisse manuell eintragen</h2>
    <form method="post">
        <table class="w3-table w3-striped">
            <thead class="w3-primary">
            <tr>
                <th>#</th>
                <th>Teamname</th>
                <th>Turnierergebnis</th>
            </tr>
            </thead>
            <?php for ($platz = 1; $platz <= $anzahl_teams; $platz++) { ?>
                <tr>
                    <td><?= $platz ?></td>
                    <td>
                        <select required class="w3-select w3-border w3-border-primary" name="team_id[<?= $platz ?>]">
                            <option disabled <?= ($turnier->details['phase'] == "ergebnis") ?: 'selected' ?>>
                                Bitte wählen
                            </option>
                            <?php foreach ($teamliste as $team_id => $team) { ?>
                                <option
                                    <?php if (($turnier_ergebnis[$platz]['team_id'] ?? 0) == $team['team_id']){ ?>selected<?php } //endif?>
                                    value="<?= $team['team_id'] ?>"><?= $team['teamname'] ?></option>
                            <?php } //end foreach?>
                        </select>
                    </td>
                    <td style="width: 30px">
                        <input type="number"
                               required
                               class="w3-input w3-border-primary w3-border"
                               value="<?= $turnier_ergebnis[$platz]['ergebnis'] ?? '' ?>"
                               name="ergebnis[<?= $platz ?>]"
                        >
                    </td>
                </tr>
            <?php } //end foreach?>
        </table>
        <p>
            <input type="submit"
                   name="ergebnis_eintragen"
                   value="Ergebnis eintragen"
                   class="w3-button w3-tertiary"
            >
        </p>
        <p>
            <input type="submit"
                   name="ergebnis_loeschen"
                   value="Ergebnis löschen"
                <?= (empty($turnier_ergebnis) ? 'disabled' : '') ?>
                   class="w3-button w3-secondary"
            >
        </p>
    </form>

<?php include '../../templates/footer.tmp.php';