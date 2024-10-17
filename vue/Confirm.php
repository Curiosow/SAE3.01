<?php
include "../controleur/UserControleur.php";
$controleur = new UserControleur();

session_start();

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $controleur->updateAccountVerification($token);
    $_SESSION['just_register_confirm'] = true;
    header('location: Login.php');
}

?>