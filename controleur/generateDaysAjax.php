<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    session_start();
    include_once("Controleur.php");
    $controleur = new Controleur();

    $realDate = new DateTime('now', new DateTimeZone('Europe/Paris'));
    $week = clone $realDate;
    if (isset($_SESSION['weekOffSet'])) {
        $week->modify(($_SESSION['weekOffSet'] * 7) . ' days');
    }

    // Start output buffering and capture the output
    ob_start();
    $controleur->generateDays($week, false, (isset($_COOKIE['collegue']) && $_COOKIE['collegue'] != "NONE"));
    $html = ob_get_clean();

    header('Content-Type: application/json');
    echo json_encode(['html' => $html]);
    exit();
}
http_response_code(405);
exit();