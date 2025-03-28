<?php
global $currentColors;
include_once("theme.php");

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
<body class="<?php echo $currentColors['subbg'] ?> flex items-center justify-center min-h-screen px-4 sm:px-6 lg:px-8">

<div class="absolute top-0 right-0 p-4">
    <!-- Bouton pour basculer le thème -->
    <form action="theme.php" method="POST" class="flex items-center">
        <input type="hidden" name="current_file" value="Error.php">
        <?php
        if ($_COOKIE['theme'] == 'light') {
            echo '<button type="submit" name="theme" value="dark" class="focus:outline-none '; echo $currentColors['text']; echo '">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.921 7.079 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>
            </button>';
        } else {
            echo '<button type="submit" name="theme" value="light" class="focus:outline-none '; echo $currentColors['text']; echo '">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
            </svg>
            </button>';
        }
        ?>
    </form>

</div>

<main class="text-center">
    <h1 class="text-2xl sm:text-4xl lg:text-5xl font-bold <?php echo $currentColors['text'] ?>">Une erreur est survenue</h1>
    <p class="mt-4 text-base sm:text-lg lg:text-xl <?php echo $currentColors['subtext'] ?>">Désolé, il est impossible de charger votre emploi du temps.</p>

    <div class="mt-8">
        <a href="Login.php" class="w-full rounded-md <?php echo $currentColors['bg'] ?> px-4 py-2 <?php echo $currentColors['text'] ?> font-semibold <?php echo $currentColors['hover'] ?>  sm:text-lg">
            Revenir à la page de connexion
        </a>
    </div>
</main>

</body>
</html>

