<?php

if (isset($_POST['theme'])) {
    $theme = $_POST['theme'];
    setcookie('theme', $theme, time() + (86400 * 30), "/");
    $_COOKIE['theme'] = $theme;

    $redirect_file = isset($_POST['current_file']) ? $_POST['current_file'] : 'Dashboard.php';
    header('location: ' . $redirect_file);
} else {
    $theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
}

$colors = [
    'light' => [
        'subbg' => 'bg-gray-200',
        'bg' => 'bg-white',
        'lines' => 'divide-gray-100',
        'text' => 'text-gray-900',
        'subtext' => 'text-gray-600',
        'unfocustext' => 'text-gray-200',
        'border' => 'border-gray-300',
        'hover' => 'hover:bg-gray-50',
        'ring' => 'ring-gray-300',
        'shadow' => 'shadow-sm',
    ],
    'dark' => [
        'subbg' => 'bg-gray-800',
        'bg' => 'bg-gray-900',
        'lines' => 'divide-gray-700',
        'text' => 'text-gray-100',
        'subtext' => 'text-gray-300',
        'unfocustext' => 'text-gray-600',
        'border' => 'border-gray-700',
        'hover' => 'hover:bg-gray-600',
        'ring' => 'ring-gray-700',
        'shadow' => 'shadow-lg',
    ],
];

$currentColors = $colors[$theme];
?>