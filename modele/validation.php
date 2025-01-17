<?php
include_once "../modele/Notification.php";
include_once "../controleur/UserControleur.php";
include_once "../controleur/NotificationControleur.php";

session_start();
$connexion = Database::getInstance()->getConnection();

if (isset($_POST['action']) && isset($_POST['justification'])) {
    $action = $_POST['action'];
    $mail = $_COOKIE['mail'];
    $justification = $_POST['justification'];

    // Vérifie si l'utilisateur est encore dans le délai
    $preparedStatement = "SELECT created_at FROM validations WHERE mail = $1";
    $result = pg_query_params($connexion, $preparedStatement, array($mail));
    if ($row = pg_fetch_assoc($result)) {
        $createdAt = new DateTime($row['created_at']);
        $now = new DateTime();
        $interval = $createdAt->diff($now);

        if ($interval->days == 0 && $interval->h < 24) {
            header("Location: ../vue/comparison.php?error=validation_expired");
            exit();
        }
    }

    // Enregistre ou met à jour la validation
    $preparedStatement = "INSERT INTO validations (mail, status, justification, created_at)
                          VALUES ($1, $2, $3, CURRENT_TIMESTAMP)
                          ON CONFLICT (\"mail\") DO UPDATE
                          SET status = $2, justification = $3, created_at = CURRENT_TIMESTAMP";
    $result = pg_query_params($connexion, $preparedStatement, array($mail, $action, $justification));

    if (!$result) {
        // Log the error message
        error_log("Error in query: " . pg_last_error($connexion));
        die("An error occurred while processing your request.");
    }

    // Vérifie si tout le monde a répondu ou si le délai est dépassé
    //$rolesString = '{GESTIONNAIRE,PROF}'; // Les rôles concernés
    //$userControleur = new UserControleur();
    //$accounts = $userControleur->getAccountsFromRole($rolesString);

    $query = "SELECT mail, status, justification, created_at FROM validations";
    $result = pg_query($connexion, $query);

    if (!$result) {
        // Log the error message
        error_log("Error in query: " . pg_last_error($connexion));
        die("An error occurred while processing your request.");
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