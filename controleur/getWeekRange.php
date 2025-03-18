<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    include_once("Controleur.php");

    $controller = new Controleur();

    $fDay = $controller->getWeekDay(true);
    $lDay = $controller->getWeekDay(false);

    $response = [
        'start' => $fDay->format('d M'),
        'end' => $lDay->format('d M')
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}