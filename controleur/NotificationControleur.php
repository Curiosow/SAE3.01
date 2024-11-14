<?php
include_once "../modele/Database.php";
include "../modele/Notification.php";

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

        $rolesArray = $this->getRoleListFromARole($role);
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

        $lastNotif = $_SESSION['lastNotif'];
        $role = $_SESSION["role"];
        $lastId = $this->getLastNotification($role);

        $rolesArray = $this->getRoleListFromARole($role);
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
        $role = $_SESSION["role"];
        $lastId = $this->getLastNotification($role);

        $preparedStatement = "UPDATE users SET lastnotif = $1 WHERE mail = $2";
        $connexion = Database::getInstance()->getConnection();
        if (!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        pg_query_params($connexion, $preparedStatement, array($lastId, $_SESSION['mail']));
    }

    public function getAllNotifications()
    {
        $preparedStatement = "SELECT * FROM notifications WHERE role = ANY($1::role[])";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communication à la base de données a échouée : ' . pg_last_error());
        }

        $role = $_SESSION["role"];
        $rolesArray = $this->getRoleListFromARole($role);
        $rolesString = '{' . implode(',', $rolesArray) . '}';

        $result = pg_query_params($connexion, $preparedStatement, array($rolesString));

        $notifications = array();
        while ($notif = pg_fetch_assoc($result)) {
            $notification = new Notification($notif['id'], $notif['title'], $notif['content']);
            array_push($notifications, $notification);
        }

        return $notifications;
    }

    public function createNotification($title, $content, $role)
    {
        $preparedStatement = "INSERT INTO notifications (title, content, role) VALUES ($1, $2, $3)";
        $connexion = Database::getInstance()->getConnection();
        if (!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        pg_query_params($connexion, $preparedStatement, array($title, $content, $role));
    }

    private function getRoleListFromARole($role)
    {
        switch ($role) {
            case "GESTIONNAIRE":
                return array('GESTIONNAIRE', 'PROF', 'ELEVE');
            case "PROF":
                return array('PROF', 'ELEVE');
            case "ELEVE":
                return array('ELEVE');
        }
    }
}