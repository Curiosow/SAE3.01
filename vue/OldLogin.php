<?php
global $currentColors;
include_once "../controleur/UserControleur.php";
include_once("theme.php");

session_start();

//$controleur = new UserControleur();
//$resources = $controleur->getAllRessources();

if(isset($_COOKIE['groupe']) && $_COOKIE['groupe'] != "NONE") {
    header('location: Dashboard.php');
    exit();
}

if(isset($_POST['groupe'])) {

    if(!isset($_COOKIE["semestre"]))
        setcookie("semestre", getSemestre($_POST['promotion'], new DateTime()), time() + 30*24*60*60, "/");
    else
        setcookie("semestre", getSemestre($_POST['promotion'], new DateTime()));

    if(!isset($_COOKIE["formation"]))
        setcookie("formation", $_POST['formation'], time() + 30*24*60*60, "/");
    else
        setcookie("formation", $_POST['formation']);

    if(!isset($_COOKIE["groupe"]))
        setcookie("groupe", $_POST['groupe'], time() + 30*24*60*60, "/");
    else
        setcookie("groupe", $_POST['groupe']);

    if(!isset($_COOKIE["sousgroupe"]))
        setcookie("sousgroupe", $_POST['sousgroupe'], time() + 30*24*60*60, "/");
    else
        setcookie("sousgroupe", $_POST['sousgroupe']);

    header('location: Dashboard.php');
    exit();
}

function getSemestre($promotion, $date)
{
    if ($date instanceof DateTime)
        $date = $date->format('Y-m-d');
    $month = date('n', strtotime($date));

    $s1 = array(1, 2, 9, 10, 11, 12);
    switch ($promotion) {
        case '1':
            return (in_array($month, $s1)) ? '1' : '2';
        case '2':
            return (in_array($month, $s1)) ? '3' : '4';
        case '3':
            return (in_array($month, $s1)) ? '5' : '6';
        default:
            return null;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emploi du temps</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body <?php echo $currentColors['subbg']; ?>>

<div class="absolute top-0 right-0 p-4">
    <!-- Bouton pour basculer le thème -->
    <form action="theme.php" method="POST" class="flex items-center">
        <input type="hidden" name="current_file" value="OldLogin.php">
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

<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <img class="mx-auto h-10 w-auto" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT7_hvZN3QSpzxIuVu8EPWSRpIZD-Al7AKMIQ&s" alt="Your Company">
        <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight <?php echo $currentColors['text']; ?>">Accédez via votre groupe</h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        <form class="space-y-6" action="OldLogin.php" method="POST">
            <div>
                <label for="promotion" class="block text-sm font-medium leading-6  <?php echo $currentColors['bg']; ?> <?php echo $currentColors['text']; ?>">Promotion</label>
                 <div class="mt-2">
                    <select id="promotion" name="promotion" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 <?php echo $currentColors['bg']; ?> <?php echo $currentColors['text']; ?> ring-1 ring-inset <?php echo $currentColors['ring']; ?>  focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option value="1">1 ère année</option>
                        <option value="2">2 ème année</option>
                        <option value="3">3 ème année</option>
                    </select>
                </div>

                <label for="formation" class="block text-sm font-medium leading-6 <?php echo $currentColors['bg']; ?> <?php echo $currentColors['text']; ?> ">Formation</label>
                <div class="mt-2">
                    <select id="formation" name="formation" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 <?php echo $currentColors['bg']; ?> <?php echo $currentColors['text']; ?> ring-1 ring-inset <?php echo $currentColors['ring']; ?> focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option value="FI">Initiale</option>
                        <option value="FA">Alternance</option>
                    </select>
                </div>

                <label for="groupe" class="block text-sm font-medium leading-6 <?php echo $currentColors['bg']; ?> <?php echo $currentColors['text']; ?> ">Groupe</label>
                <div class="mt-2">
                    <select id="groupe" name="groupe" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 ring-1 ring-inset <?php echo $currentColors['bg']; ?> <?php echo $currentColors['text']; ?> <?php echo $currentColors['ring']; ?> focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                    </select>
                </div>

                <label for="sousgroupe" class="block text-sm font-medium leading-6 <?php echo $currentColors['bg']; ?> <?php echo $currentColors['text']; ?> ">Sous-Groupe</label>
                <div class="mt-2">
                    <select id="sousgroupe" name="sousgroupe" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 <?php echo $currentColors['bg']; ?> <?php echo $currentColors['text']; ?>  ring-1 ring-inset <?php echo $currentColors['ring']; ?>  focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option value="1">1</option>
                        <option value="2">2</option>
                    </select>
                </div>
            </div>

            <div>
                <button type="submit" class="flex w-full justify-center rounded-md <?php echo $currentColors['bg']; ?> px-3 py-1.5 text-sm font-semibold leading-6 <?php echo $currentColors['text']; ?> shadow-sm <?php echo $currentColors['hover']; ?> focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:<?php echo $currentColors['ring']; ?>">Connexion</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>