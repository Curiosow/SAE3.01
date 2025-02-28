<?php
include_once "../controleur/pdf/fpdf.php";
include_once "../modele/managers/ScheduleManager.php";
include_once "../controleur/AbsenceControleur.php";
include_once "../controleur/UserControleur.php";

$absenceControleur = new AbsenceControleur();

function disconnect() {
    session_destroy();

    unset($_COOKIE['role']);
    setcookie("role", "NONE");

    unset($_COOKIE['groupe']);
    setcookie("groupe", "NONE");

    unset($_COOKIE['logged']);
    setcookie("logged", "NONE");

    unset($_COOKIE['mail']);
    setcookie("mail", "NONE");

    unset($_COOKIE['collegue']);
    setcookie("collegue", "NONE");

    unset($_COOKIE['line']);
    setcookie("line", "NONE");

    unset($_COOKIE['semestre']);
    setcookie("semestre", "NONE");

    unset($_COOKIE['formation']);
    setcookie("formation", "NONE");

    unset($_COOKIE['sousgroupe']);
    setcookie("sousgroupe", "NONE");

    unset($_COOKIE['nom']);
    setcookie("nom", "NONE");

    unset($_COOKIE['prenom']);
    setcookie("prenom", "NONE");

    unset($_COOKIE['civilite']);
    setcookie("civilite", "NONE");
    header('location: Login.php');
    exit();
}

function createAbsence($notificationsControleur, $start_date, $end_date, $reason) {
    global $absenceControleur;
    $id = $_COOKIE['mail'];
    if(isset($_COOKIE['collegue']))
        $id = $_COOKIE['collegue'];

    $start_dateDT = DateTime::createFromFormat('d-m-Y H:i', $start_date)->format('Y-m-d H:i:s');
    $end_dateDT = DateTime::createFromFormat('d-m-Y H:i', $end_date)->format('Y-m-d H:i:s');

    $absenceControleur->addAbsence($start_dateDT, $end_dateDT, $reason);
    $notificationsControleur->createNotification("Demande de changement d'emploi du temps", $id . " ne sera pas présent du " . $start_date . " jusqu'au " . $end_date . " pour le motif : " . $reason . ".", "GESTIONNAIRE", true);
}

function notifModificationStudent($notificationsControleur) {
    $notificationsControleur->createNotification("Changement d'emploi du temps", "Une modification de votre emploi du temps a été effectuée.", "ELEVE", false);
}

function notifNewVersion($notificationsControleur)
{
    $message = "Une nouvelle proposition d'emploi du temps est disponible, veuillez y laisser votre avis.\n Lien : <a href='Comparison.php' style='color: blue'>Comparer</a>";
    $notificationsControleur->createNotification("Changement d'emploi du temps", $message, "PROF", false);
}

function changeGroupe($newGroupe)
{
    $userControleur = new UserControleur();
    $resource = $userControleur->getRessourceFromName($newGroupe);

    if (!isset($_COOKIE["semestre"]))
        setcookie("semestre", $resource['semestre'], time() + 30 * 24 * 60 * 60, "/");
    else
        setcookie("semestre", $resource['semestre']);

    if (!isset($_COOKIE["formation"]))
        setcookie("formation", $resource['typeformation'], time() + 30 * 24 * 60 * 60, "/");
    else
        setcookie("formation", $resource['typeformation']);

    if ($resource['parcours'] == 'AB')
        $groupe = substr($resource['nomressource'], 0, 1);
    else
        $groupe = $resource['parcours'];

    if (!isset($_COOKIE["groupe"]))
        setcookie("groupe", $groupe, time() + 30 * 24 * 60 * 60, "/");
    else
        setcookie("groupe", $groupe);

    if (!isset($_COOKIE["sousgroupe"]))
        setcookie("sousgroupe", substr($resource['nomressource'], -1), time() + 30 * 24 * 60 * 60, "/");
    else
        setcookie("sousgroupe", substr($resource['nomressource'], -1));


    header('location: Dashboard.php');
    exit();
}

function getRoleListFromARole($role) {
    switch ($role) {
        case "GESTIONNAIRE":
            return array('GESTIONNAIRE', 'PROF', 'ELEVE');
        case "PROF":
            return array('PROF', 'ELEVE');
        case "ELEVE":
            return array('ELEVE');
    }
}

function getCalendarPdf($date) {
    $pdf = new FPDF();
    $pdf->AddPage('L');
    $pdf->SetFont('Arial', 'B', 12);

    $pdf->Cell(0, 10, 'Emploi du temps de la semaine', 0, 1, 'C');

    $pdf->Rect(10, 20, 270, 165);

    // header du calendrier
    $pdf->SetFont('Arial', 'B', 12);
    $daysOfWeek = [' ', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
    foreach ($daysOfWeek as $day) {
        $pdf->Cell(45, 15, $day, 1, 0, 'C');
    }
    $pdf->Ln();

    // timings
    $pdf->SetFont('Arial', '', 12);
    $timeSlots = [
        '08:00', '09:00', '10:00', '11:00',
        '12:00', '13:00', '14:00', '15:00',
        '16:00', '17:00'
    ];

    foreach ($timeSlots as $timeSlot) {
        $pdf->Cell(45, 15, $timeSlot, 1);
        for ($i = 0; $i < 5; $i++) {
            $pdf->Cell(45, 15); // No border for empty cells
        }
        $pdf->Ln();
    }

    // Remplissage cours
    $weekDates = getWeekDates($date);
    foreach ($weekDates as $day) {
        $courses = getDay($day, $day->format('d'), $_COOKIE['semestre'], $_COOKIE['groupe'], (int) $_COOKIE['sousgroupe'], $_COOKIE['formation']);
        foreach ($courses as $course) {

            $horraire = new DateTime($course->getHoraire(), new DateTimeZone('Europe/Paris'));
            $dayOfWeek = $horraire->format('N') - 1; // 0 = Lundi | 6 = Dimanche
            $timeSlotIndex = (int)$horraire->format('H') - 8;
            $hourOfTheLesson = (((int)$horraire->format('H') - 8 ) * 2) + ((int) $horraire->format('i') / 30);

            $duration = countMinutes($course->getDuration()) / 30;

            if ($timeSlotIndex >= 0 && $timeSlotIndex < count($timeSlots)) {
                $pdf->SetXY(10 + 45 * ($dayOfWeek + 1), 35 + 7.5 * $hourOfTheLesson);
                $str = iconv('UTF-8', 'windows-1252', $course->getEnseignementShortName());
                $pdf->Cell(45, 7.5 * $duration, $str, 1, 0, 'C');
            }
        }
    }

    $pdf->Output('D', 'Emploi_du_temps_semaine.pdf');
}

function getCalendarIcal($date) {
    // Evenèment au format ICS
    $ics = "BEGIN:VCALENDAR\n";
    $ics .= "VERSION:2.0\n";
    $ics .= "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\n";

    $weekDates = getWeekDates($date);
    foreach ($weekDates as $day) {
        $courses = getDay($day, $day->format('d'), $_COOKIE['semestre'], $_COOKIE['groupe'], (int) $_COOKIE['sousgroupe'], $_COOKIE['formation'], (isset($_COOKIE['collegue']) && $_COOKIE['collegue'] != "NONE"));
        $alreadyPlace = [];
        foreach ($courses as $course) {
            if (in_array($course, $alreadyPlace)) continue;

            $alreadyPlace[] = $course;

            $horraire = new DateTime($course->getHoraire(), new DateTimeZone('Europe/Paris'));
            $horraire->modify('+1 hour');

            $test = $horraire->getTimestamp();
            $endTimestamp = getEndTimestamp($test, $course->getDuration());

            // Variables
            $date_debut = $test;
            $date_fin = $endTimestamp;
            $objet = $course->getEnseignementShortName();

            $lieu = 'Pas de salle';
            if ($course->getSalle() !== null) $lieu = 'Salle ' . $course->getSalle();
            if ($course->getSalle() == '200') $lieu = 'Amphi.';

            $details = $course->getEnseignementLongName() . ' - ' . $course->getCollegueFullName();

            $ics .= "BEGIN:VEVENT\n";
            $ics .= "X-WR-TIMEZONE:Europe/Paris\n";
            $ics .= "DTSTART:" . date('Ymd', $date_debut) . "T" . date('His', $date_debut) . "\n";
            $ics .= "DTEND:" . date('Ymd', $date_fin) . "T" . date('His', $date_fin) . "\n";
            $ics .= "SUMMARY:" . $objet . "\n";
            $ics .= "LOCATION:" . $lieu . "\n";
            $ics .= "DESCRIPTION:" . $details . "\n";
            $ics .= "END:VEVENT\n";
        }
    }
    $ics .= "END:VCALENDAR\n";

    // Création du fichier

    /*
     * Si il y a une erreur de permission sur la création/utilisation du fichier :
     *
     * chmod 755 /var/www/html/
     * chown www-data:www-data /var/www/html/
     *
     * Ces deux commandes donnent les permissions nécessaires.
     */

    $fichier = '../Emploi_du_temps_semaine.ics';
    $f = fopen($fichier, 'w+');
    if ($f === false) {
        die('Erreur lors de l\'ouverture du fichier pour écriture.');
    }
    if (fputs($f, $ics) === false) {
        die('Erreur lors de l\'écriture dans le fichier.');
    }
    fclose($f);

    if (!file_exists($fichier)) {
        die('Le fichier n\'a pas été créé.');
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($fichier) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($fichier));

    readfile($fichier);

    unlink($fichier);
}

function countMinutes($time) {
    $timeParts = explode(':', $time);
    $hours = $timeParts[0];
    $minutes = $timeParts[1];
    return $hours * 60 + $minutes;
}

function getEndTimestamp($startTimestamp, $duration) {
    $durationParts = explode(':', $duration);
    $hours = (int)$durationParts[0];
    $minutes = (int)$durationParts[1];
    $totalMinutes = ($hours * 60) + $minutes;
    return $startTimestamp + ($totalMinutes * 60);
}

function returnVersion() {
    return getVersion();
}