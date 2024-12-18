<?php
include_once "../controleur/pdf/fpdf.php";
include_once "../modele/managers/ScheduleManager.php";

function disconnect() {
    session_destroy();
    header('location: Login.php');
    exit();
}

function createAbsence($notificationsControleur, $start_date, $end_date, $reason) {
    $id = $_SESSION['mail'];
    if(isset($_SESSION['collegue']))
        $id = $_SESSION['collegue'];

    $start_date = DateTime::createFromFormat('d-m-Y H:i', $start_date)->format('d-m-Y H:i');
    $end_date = DateTime::createFromFormat('d-m-Y H:i', $end_date)->format('d-m-Y H:i');

    $notificationsControleur->createNotification("Demande de changement d'emploi du temps", $id . " ne sera pas présent du " . $start_date . " jusqu'au " . $end_date . " pour le motif : " . $reason . ".", "GESTIONNAIRE", true);
}

function notifModificationStudent($notificationsControleur) {
    $notificationsControleur->createNotification("Changement d'emploi du temps", "Une modification de votre emploi du temps a été effectuée.", "ELEVE", false);
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

    // Title
    $pdf->Cell(0, 10, 'Emploi du temps de la semaine', 0, 1, 'C');

    // Days of the week header
    $pdf->SetFont('Arial', 'B', 12);
    $daysOfWeek = [' ', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven'];
    foreach ($daysOfWeek as $day) {
        $pdf->Cell(45, 15, $day, 1, 0, 'C');
    }
    $pdf->Ln();

    // Time slots and courses
    $pdf->SetFont('Arial', '', 12);
    $timeSlots = [
        '08:00', '09:00', '10:00', '11:00',
        '12:00', '13:00', '14:00', '15:00',
        '16:00', '17:00'
    ];

    foreach ($timeSlots as $timeSlot) {
        $pdf->Cell(45, 15, $timeSlot, 1);
        for ($i = 0; $i < 5; $i++) {
            $pdf->Cell(45, 15, '', 1); // Empty cells for each day of the week
        }
        $pdf->Ln();
    }

    // Fill in the courses
    $weekDates = getWeekDates($date);
    foreach ($weekDates as $day) {
        $courses = getDay($day, $day->format('d'), $_SESSION['semestre'], $_SESSION['groupe'], (int) $_SESSION['sousgroupe'], $_SESSION['formation']);
        foreach ($courses as $course) {
            $horraire = new DateTime($course->getHoraire(), new DateTimeZone('Europe/Paris'));
            $dayOfWeek = $horraire->format('N') - 1; // 0 (Mon) to 6 (Sun)
            $timeSlotIndex = (int)$horraire->format('H') - 8; // Assuming time slots start at 08:00

            if ($timeSlotIndex >= 0 && $timeSlotIndex < count($timeSlots)) {
                $pdf->SetXY(10 + 45 * ($dayOfWeek + 1), 35 + 15 * $timeSlotIndex);
                $str = iconv('UTF-8', 'windows-1252', $course->getenseignementShortName());
                $pdf->Cell(45, 15, $str, 1, 0, 'C');
                //$pdf->Cell(45, 15, $course->getSalle(), 1, 1, 'C');
            }
        }
    }

    $pdf->Output('D', 'Emploi_du_temps_semaine.pdf');
}

function returnVersion() {
    return getVersion();
}