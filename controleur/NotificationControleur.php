<?php
include_once "../modele/Notification.php";
include_once "../controleur/UserControleur.php";
include_once "../controleur/UtilsControleur.php";

$userControleur = new UserControleur();

class NotificationControleur
{

    public function __construct()
    {
    }

    public function getLastNotification($role)
    {
        $preparedStatement = "SELECT MAX(id) as lastId FROM notifications WHERE role = ANY($1::role[])";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communication à la base de données a échouée : ' . pg_last_error());
        }

        $rolesArray = getRoleListFromARole($role);
        $rolesString = '{' . implode(',', $rolesArray) . '}';
        $result = pg_query_params($connexion, $preparedStatement, array($rolesString));

        return pg_fetch_result($result, 0, 0);
    }

    public function getUnreadNotifications()
    {
        $preparedStatement = "SELECT * FROM notifications WHERE id > $1 AND id <= $2 AND role = ANY($3::role[])";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communication à la base de données a échouée : ' . pg_last_error());
        }

        $lastNotif = $_COOKIE['lastNotif'];
        $role = $_COOKIE["role"];
        $lastId = $this->getLastNotification($role);

        $rolesArray = getRoleListFromARole($role);
        $rolesString = '{' . implode(',', $rolesArray) . '}';
        $result = pg_query_params($connexion, $preparedStatement, array($lastNotif, $lastId, $rolesString));

        $notifications = array();
        while ($notif = pg_fetch_assoc($result)) {
            $notification = new Notification($notif['id'], $notif['title'], $notif['content']);
            array_push($notifications, $notification);
        }

        return $notifications;
    }

    public function setToLastNotification()
    {
        $role = $_COOKIE["role"];
        $lastId = $this->getLastNotification($role);

        $preparedStatement = "UPDATE users SET lastnotif = $1 WHERE mail = $2";
        $connexion = Database::getInstance()->getConnection();
        if (!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        pg_query_params($connexion, $preparedStatement, array($lastId, $_COOKIE['mail']));
    }

    public function getAllNotifications()
    {
        $preparedStatement = "SELECT * FROM notifications WHERE role = ANY($1::role[])";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communication à la base de données a échouée : ' . pg_last_error());
        }

        $role = $_COOKIE["role"];
        $rolesArray = getRoleListFromARole($role);
        $rolesString = '{' . implode(',', $rolesArray) . '}';

        $result = pg_query_params($connexion, $preparedStatement, array($rolesString));

        $notifications = array();
        while ($notif = pg_fetch_assoc($result)) {
            $notification = new Notification($notif['id'], $notif['title'], $notif['content']);
            array_push($notifications, $notification);
        }

        return $notifications;
    }


    public function createNotification($title, $content, $role, $sendMail)
    {
        global $userControleur;
        $preparedStatement = "INSERT INTO notifications (title, content, role) VALUES ($1, $2, $3)";
        $connexion = Database::getInstance()->getConnection();
        if (!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        pg_query_params($connexion, $preparedStatement, array($title, $content, $role));

        if($sendMail) {
            $api_key = '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';
            $subject = 'Notification - ' . $title;
            $message = "Vous avez reçu une notification !\n" . $content . "\n\nConnectez vous afin de lire cette notification.";

            $accounts = $userControleur->getAccountsFromRole($role);
            $mails = array();
            foreach ($accounts as $user) {
                $mails[] = $user['mail'];
                $userControleur->testUser($user['id']);
            }

            $data = array(
                'sender' => array('name' => 'IUT Maubeuge - Emploi du temps', 'email' => 'bouttieroscar@gmail.com'),
                'to' => array_map(function ($email) {
                    return ['email' => $email];
                }, $mails),
                'subject' => $subject,
                'textContent' => $message
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.sendinblue.com/v3/smtp/email');
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'api-key: ' . $api_key,
                'Content-Type: application/json'
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                header('location: Error.php');
                exit();
            }
            curl_close($ch);
        }
    }
}