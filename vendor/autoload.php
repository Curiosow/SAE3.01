<?php

// Register The Composer Auto Loader
require __DIR__ . '/../vendor/autoload.php';

// Check if running in CLI mode and handle specific commands
if (php_sapi_name() == "cli" && isset($_SERVER['argv'])) {
    $argv = $_SERVER['argv'];
    if (in_array('artisan', $argv) && in_array('clear-compiled', $argv)) {
        $files = glob(__DIR__ . '/../bootstrap/cache/*');
        foreach ($files as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
        exit();
    }
}

// Include The Compiled Class File if it exists
$compiledPath = __DIR__ . '/../cache/compiled.php';
if (file_exists($compiledPath)) {
    require $compiledPath;
}