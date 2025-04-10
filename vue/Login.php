<?php
global $currentColors;
include_once "../controleur/UserControleur.php";
include_once "../controleur/NotificationControleur.php";
include_once "../modele/managers/CollegueManager.php";
include_once("theme.php");

$controleur = new UserControleur();
$notificationControleur = new NotificationControleur();

session_start();

// Vérification si le thème n'est pas initialiser
if(!isset($_COOKIE['theme'])) {
    $_COOKIE['theme'] = 'light';
}

if(isset($_COOKIE['logged']) && $_COOKIE['logged'] != "NONE") {
    header('location: Dashboard.php');
    exit();
}

if(isset($_POST['email-address']) && isset($_POST['password'])) {
    $mail = $_POST['email-address'];
    $password = $_POST['password'];

    $login_err = "";
    if($controleur->hasAccount($mail)) {
        $data = pg_fetch_assoc($controleur->getAccountFromMail($mail));
        if(password_verify(trim($password), $data['password'])) {
            if($data['verified']) {

                if(!isset($_COOKIE['logged']))
                    setcookie("logged", true, time() + 30*24*60*60, "/");
                else
                    setcookie("logged", true);

                if(!isset($_COOKIE['mail']))
                    setcookie("mail", $data['mail'], time() + 30*24*60*60, "/");
                else
                    setcookie("mail", $data['mail']);

                if(!isset($_COOKIE['role']))
                    setcookie("role", $data['role'], time() + 30*24*60*60, "/");
                else
                    setcookie("role", $data['role']);

                if(isACollegue($data['mail'])) {
                    if(!isset($_COOKIE['collegue']))
                        setcookie("collegue", getCollegueId($data['mail']), time() + 30*24*60*60, "/");
                    else
                        setcookie("collegue", getCollegueId($data['mail']));
                }

                if(!isset($_COOKIE['lastNotif']))
                    setcookie("lastNotif", $data['lastnotif'], time() + 30*24*60*60, "/");
                else
                    setcookie("lastNotif", $data['lastnotif']);

                $line = getLineFromCSVByEmail($mail);

                if(!isset($_COOKIE['semestre']))
                    setcookie("semestre", $line[3], time() + 30*24*60*60, "/");
                else
                    setcookie("semestre", $line[3]);

                $formation = 'FI';
                if(strpos($line[4], 'FA') === 0)
                    $formation = 'FA';

                if(!isset($_COOKIE['formation']))
                    setcookie("formation", $formation, time() + 30*24*60*60, "/");
                else
                    setcookie("formation", $formation);

                $group = removePrefix($line[4]);

                if(!isset($_COOKIE['groupe']))
                    setcookie("groupe", $group[0], time() + 30*24*60*60, "/");
                else
                    setcookie("groupe", $group[0]);

                if(!isset($_COOKIE['sousgroupe']))
                    setcookie("sousgroupe", $group[1], time() + 30*24*60*60, "/");
                else
                    setcookie("sousgroupe", $group[1]);

                if(!isset($_COOKIE['nom']))
                    setcookie("nom", $line[1], time() + 30*24*60*60, "/");
                else
                    setcookie("nom", $line[1]);

                if(!isset($_COOKIE['prenom']))
                    setcookie("prenom", $line[2], time() + 30*24*60*60, "/");
                else
                    setcookie("prenom", $line[2]);

                if(!isset($_COOKIE['civilite']))
                    setcookie("civilite", $line[0], time() + 30*24*60*60, "/");
                else
                    setcookie("civilite", $line[0]);

                header('location: Dashboard.php');
                exit();
            } else {
                $login_err = "Vous devez d'abord valider votre compte!";
            }
        } else {
            $login_err = "Adresse mail ou mot de passe incorrect!";
        }
    } else {
        $login_err = "Adresse mail ou mot de passe incorrect!";
    }
}

function removePrefix($string) {
    if (strpos($string, 'FI') === 0) {
        return substr($string, 2);
    } elseif (strpos($string, 'FA') === 0) {
        return substr($string, 2);
    }
    return $string;
}

function getLineFromCSVByEmail($email) {
    $file = fopen('../liste_groupes.csv', 'r');
    if ($file !== false) {
        while (($line = fgetcsv($file, 1000, ';', '"', '\\')) !== false) {
            if (isset($line[5]) && strcasecmp(trim($line[5]), $email) === 0) {
                fclose($file);
                return $line;
            }
        }
        fclose($file);
    }
    return null;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emploi du temps</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="<?php echo $currentColors['subbg'] ?> flex items-center justify-center min-h-screen px-4 sm:px-6 lg:px-8">

<div class="absolute top-0 right-0 p-4">
    <!-- Bouton pour basculer le thème -->
    <form action="theme.php" method="POST" class="flex items-center">
        <input type="hidden" name="current_file" value="Login.php">
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

<main class="w-full max-w-sm mx-auto text-center">

    <img class="mx-auto h-10 w-auto" src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/bd/UPHF_logo.svg/2560px-UPHF_logo.svg.png">
    <h2 class="text-2xl font-bold leading-9 tracking-tight <?php echo $currentColors['text']; ?>">Connectez-vous avec votre mail UPHF</h2>

    <?php
    if(isset($_SESSION['just_register']) && $_SESSION['just_register']) {
        $_SESSION['just_register'] = false;
        echo '<div class="rounded-md bg-green-50 p-4">
                      <div class="flex">
                        <div class="flex-shrink-0">
                          <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                          </svg>
                        </div>
                        <div class="ml-3">
                          <h3 class="text-sm font-medium text-green-800">Enregistrement effectué avec succès !</h3>
                          <div class="mt-2 text-sm text-green-700">
                            <ul role="list" class="list-disc space-y-1 pl-5">
                              <li>Vous avez reçu un mail de confirmation.</li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>';
    }

    if(isset($_SESSION['just_register_confirm']) && $_SESSION['just_register_confirm']) {
        $_SESSION['just_register_confirm'] = false;
        echo '<div class="rounded-md bg-green-50 p-4">
                      <div class="flex">
                        <div class="flex-shrink-0">
                          <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                          </svg>
                        </div>
                        <div class="ml-3">
                          <h3 class="text-sm font-medium text-green-800">Enregistrement effectué avec succès !</h3>
                          <div class="mt-2 text-sm text-green-700">
                            <ul role="list" class="list-disc space-y-1 pl-5">
                              <li>Votre mail a été confirmé, il vous suffit de vous connecter.</li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>';
    }

    if(!empty($login_err)) {
        echo '<div class="rounded-md bg-red-50 p-4">
                      <div class="flex">
                        <div class="flex-shrink-0">
                          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                          </svg>
                        </div>
                        <div class="ml-3">
                          <h3 class="text-sm font-medium text-red-800">Une erreur est survenue.</h3>
                          <div class="mt-2 text-sm text-red-700">
                            <ul role="list" class="list-disc space-y-1 pl-5">
                              <li>'. $login_err . '</li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>';
    }
    ?>

    <form class="space-y-6 mt-6" action="Login.php" method="POST">
        <div class="space-y-4">
            <div>
                <label for="email-address" class="sr-only">Adresse mail UPHF</label>
                <input id="email-address" name="email-address" type="email" autocomplete="email" required
                       class="block w-full rounded-md <?php echo $currentColors['bg'] ?> <?php echo $currentColors['border'] ?> shadow-sm py-2 px-3 <?php echo $currentColors['text'] ?> focus:ring-indigo-600 focus:border-indigo-600 sm:text-base"
                       placeholder="Adresse mail UPHF">
            </div>
            <div>
                <label for="password" class="sr-only">Mot de passe</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       class="block w-full rounded-md <?php echo $currentColors['bg'] ?> <?php echo $currentColors['border'] ?> shadow-sm py-2 px-3 <?php echo $currentColors['text'] ?> focus:ring-indigo-600 focus:border-indigo-600 sm:text-base"
                       placeholder="Mot de passe">
            </div>
        </div>

        <button type="submit" class="w-full rounded-md <?php echo $currentColors['bg'] ?> px-4 py-2 <?php echo $currentColors['text'] ?> font-semibold <?php echo $currentColors['hover'] ?>  sm:text-lg">
            S'enregistrer
        </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-4">
        Vous avez oublié votre mot de passe ?
        <a href="ForgotPassword.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Changez-le moi</a>
    </p>

    <p class="text-center text-sm text-gray-500 mt-4">
        Vous n'avez pas de compte ?
        <a href="Register.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Enregistrez-vous ici</a>
    </p>

    <p class="text-center text-sm text-gray-500 mt-4">
        Vous n'êtes pas enregistré ?
        <a href="OldLogin.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Cherchez votre EDT</a>
    </p>

</main>

</body>
</html>