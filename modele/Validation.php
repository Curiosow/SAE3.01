<?php
include_once "../modele/Notification.php";
include_once "../controleur/UserControleur.php";
include_once "../controleur/NotificationControleur.php";

session_start();
$connexion = Database::getInstance()->getConnection();

if(!$connexion) {
    die('La communcation à la base de données a echouée : ' . pg_last_error());
}


if (isset($_POST['action']) && isset($_POST['justification'])) {
    $action = $_POST['action'];
    $mail = $_COOKIE['mail'];
    $justification = $_POST['justification'];

    // Enregistre ou met à jour la validation
    $preparedStatement = "INSERT INTO validations (mail, status, justification)
                          VALUES ($1, $2, $3)
                          ON CONFLICT (\"mail\") DO UPDATE
                          SET status = $2, justification = $3";
    pg_query_params($connexion, $preparedStatement, array($mail, $action, $justification));

    $getAllResponses = "SELECT mail, status, justification, created_at FROM validations";
    $result = pg_query($connexion, $getAllResponses);

    if (!$result) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $responses = [];
    $pending = [];
    $refused = [];
    $allAccepted = true;

    while ($row = pg_fetch_assoc($result)) {
        $responses[$row['mail']] = $row;

        $createdAt = new DateTime($row['created_at']);
        $now = new DateTime();
        $interval = $createdAt->diff($now);

        if (is_null($row['status']) && ($interval->days > 0 || $interval->h >= 24)) {
            $pending[] = $row['mail']; // Utilisateurs hors délai
        } elseif ($row['status'] === 'REFUSE') {
            $refused[] = [
                'mail' => $row['mail'],
                'justification' => $row['justification']
            ];
            $allAccepted = false;
        } elseif (is_null($row['status'])) {
            $allAccepted = false;
        }
    }

    // Notification pour les validations complètes ou partielles
    $notificationControleur = new NotificationControleur();
    if ($allAccepted && empty($pending)) {
        $notificationControleur->createNotification(
            "Validation complète",
            "Tous les utilisateurs ont validé les changements.",
            'GESTIONNAIRE',
            true
        );
    } elseif (!$allAccepted) {
        $content = "Certains utilisateurs ont refusé les changements. Détails :\n";
        foreach ($refused as $refusal) {
            $content .= "- " . $_COOKIE['mail'] . ": " . $refusal['justification'] . "\n";
        }

        $notificationControleur->createNotification(
            "Validation partielle",
            $content,
            'GESTIONNAIRE',
            true
        );
    }

    header("Location: ../vue/comparison.php");
    exit();
} else {
    header('location: ../vue/Error.php');
}
?>