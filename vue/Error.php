<?php

session_start();
session_destroy();

?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Erreur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="h-full flex items-center justify-center bg-gray-100 px-4 sm:px-6 lg:px-8">

<main class="text-center">
    <h1 class="text-2xl sm:text-4xl lg:text-5xl font-bold text-gray-900">Une erreur est survenue</h1>
    <p class="mt-4 text-base sm:text-lg lg:text-xl text-gray-600">Désolé, il est impossible de charger votre emploi du temps.</p>

    <div class="mt-8">
        <a href="Login.php" class="inline-block rounded-md bg-gray-600 px-6 py-3 text-sm sm:text-base lg:text-lg font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
            Revenir à la page de connexion
        </a>
    </div>
</main>

</body>
</html>

