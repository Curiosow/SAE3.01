<?php
include_once "../modele/Notification.php";
include_once "../controleur/UserControleur.php";
include_once "../controleur/NotificationControleur.php";

session_start();
$connexion = Database::getInstance()->getConnection();

$userId = $_SESSION['user_id']; // Récupère l'ID utilisateur depuis la session
$action = $_POST['action'];
$justification = $_POST['justification'] ?? null;

// Vérifie si l'utilisateur est encore dans le délai
$preparedStatement = "SELECT created_at FROM validations WHERE user_id = $1";
$result = pg_query_params($connexion, $preparedStatement, array($userId));
if ($row = pg_fetch_assoc($result)) {
    $createdAt = new DateTime($row['created_at']);
    $now = new DateTime();
    $interval = $createdAt->diff($now);

    if ($interval->days == 0 && $interval->h < 24) {
        header("Location: comparaison.php?error=validation_expired");
        exit();
    }
}

// Enregistre ou met à jour la validation
$preparedStatement = "INSERT INTO validations (user_id, status, justification, created_at)
                      VALUES ($1, $2, $3, CURRENT_TIMESTAMP)
                      ON CONFLICT (user_id) DO UPDATE 
                      SET status = $2, justification = $3, created_at = CURRENT_TIMESTAMP";
pg_query_params($connexion, $preparedStatement, array($userId, $action, $justification));

// Vérifie si tout le monde a répondu ou si le délai est dépassé
$rolesString = '{GESTIONNAIRE,PROF}'; // Les rôles concernés
$userControleur = new UserControleur();
$accounts = $userControleur->getAccountsFromRole($rolesString);

$query = "SELECT user_id, status, justification, created_at FROM validations";
$result = pg_query($connexion, $query);

$responses = [];
$pending = [];
$refused = [];
$allAccepted = true;

while ($row = pg_fetch_assoc($result)) {
    $responses[$row['user_id']] = $row;

    $createdAt = new DateTime($row['created_at']);
    $now = new DateTime();
    $interval = $createdAt->diff($now);

    if (is_null($row['status']) && ($interval->days > 0 || $interval->h >= 24)) {
        $pending[] = $row['user_id']; // Utilisateurs hors délai
    } elseif ($row['status'] === 'REFUSE') {
        $refused[] = [
            'user' => $row['user_id'],
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
        '{GESTIONNAIRE}',
        true
    );
} elseif (!$allAccepted) {
    $content = "Certains utilisateurs ont refusé les changements. Détails :\n";
    foreach ($refused as $refusal) {
        $user = $userControleur->getUserById($refusal['user']); // À implémenter si non existant
        $content .= "- " . $user['mail'] . ": " . $refusal['justification'] . "\n";
    }

    $notificationControleur->createNotification(
        "Validation partielle",
        $content,
        '{GESTIONNAIRE}',
        true
    );
}

header("Location: comparaison.php");
exit();
?>