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
        $preparedStatement = "SELECT MAX(id) as lastId FROM notifications WHERE role IN ($1)";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        $rolesArray = $this->getRoleListFromARole($role);
        $result = pg_query_params($connexion, $preparedStatement, array($rolesArray));

        return pg_fetch_result($result, 0, 0);
    }

    public function getUnreadNotifications()
    {
        $preparedStatement = "SELECT * FROM notifications WHERE id > $1 AND id <= $2 AND role in ($3)";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        $lastNotif = $_SESSION['lastNotif'];
        $role = $_SESSION["role"];
        $lastId = $this->getLastNotification($role);

        $rolesArray = $this->getRoleListFromARole($role);
        $result = pg_query_params($connexion, $preparedStatement, array($lastNotif, $lastId, $rolesArray));

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
        if(!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        pg_query_params($connexion, $preparedStatement, array($lastId, $_SESSION['mail']));
    }

    public function getAllNotifications()
    {
        $preparedStatement = "SELECT * FROM notifications WHERE role IN ($1)";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        $role = $_SESSION["role"];
        $rolesArray = $this->getRoleListFromARole($role);

        $result = pg_query_params($connexion, $preparedStatement, array($rolesArray));

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
        if(!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        pg_query_params($connexion, $preparedStatement, array($title, $content, $role));
    }

    private function getRoleListFromARole($role)
    {
        $roleList = array();
        switch ($role) {
            case "GESTIONNAIRE":
                array_push($roleList, "GESTIONNAIRE", "PROF", "ELEVE");
                break;
            case "PROF":
                array_push($roleList, "PROF", "ELEVE");
                break;
            case "ELEVE":
                array_push($roleList, "ELEVE");
                break;
        }

        return implode(", ", $roleList);
    }
}