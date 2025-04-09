<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    include_once("Controleur.php");
    $controller = new Controleur();

    $days = [
        "lundi" => $controller->getDayWeek('monday'),
        "mardi" => $controller->getDayWeek('tuesday'),
        "mercredi" => $controller->getDayWeek('wednesday'),
        "jeudi" => $controller->getDayWeek('thursday'),
        "vendredi" => $controller->getDayWeek('friday'),
    ];

    $response = [];

    foreach ($days as $key => $dateObj) {
        $response[$key] = [
            "date" => $dateObj->format("Y-m-d"),
            "display" => $dateObj->format("d M")
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}