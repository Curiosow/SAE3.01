<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['sidebarState'])) {
    $_SESSION['sidebarState'] = $data['sidebarState'];
}

