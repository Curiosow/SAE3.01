<?php

session_start();
session_destroy();

?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Erreur</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full">
<main class="grid min-h-full place-items-center bg-white px-6 py-24 sm:py-32 lg:px-8">
    <div class="text-center">
        <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-5xl">Une erreur est survenue</h1>
        <p class="mt-6 text-base leading-7 text-gray-600">Désolé, il est impossible de charger votre emploi du temps.</p>
        <div class="mt-10 flex items-center justify-center gap-x-6">
            <a href="Login.php" class="rounded-md bg-gray-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">Revenir à la page de connexion</a>
        </div>
    </div>
</main>
</body>
</html>