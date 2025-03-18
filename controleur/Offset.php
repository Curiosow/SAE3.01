<?php

session_start();

// Données de bases
setlocale(LC_TIME, 'fr_FR.UTF-8');
$realDate = new DateTime('now', new DateTimeZone('Europe/Paris'));
$date = clone $realDate;
$week = clone $realDate;

// Vérification si l'utilisateur à demander de changer de semaine
if (isset($_POST['weekOffSet'])) {
    $_SESSION['weekOffSet'] = (int)$_POST['weekOffSet'];
} else {
    // Si ce n'est pas le cas, si aucune semaine n'est enregistrer dans la session, alors on définit à la semaine actuelle.
    if (!isset($_SESSION['weekOffSet'])) {
        $_SESSION['weekOffSet'] = 0;
    }
}

if (isset($_GET['offset'])) {
    $offset = (int)$_GET['offset'];
    if ($offset === -1) {
        echo json_encode($_SESSION['weekOffSet'] - 1);
    } elseif ($offset === 1) {
        echo json_encode($_SESSION['weekOffSet'] + 1);
    } else {
        echo json_encode("Invalid offset");
    }
}


