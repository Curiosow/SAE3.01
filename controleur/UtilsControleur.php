<?php
include_once("../modele/managers/ScheduleManager.php");

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

function returnVersion() {
    return getVersion();
}