<?php
include_once "../controleur/UserControleur.php";

$controleur = new UserControleur();
$resources = $controleur->getAllRessources();

$resourcesArray = array();
while ($row = pg_fetch_assoc($resources)) {
    $resourcesArray[] = $row;
}

header('Content-Type: application/json');
echo json_encode($resourcesArray);
?>