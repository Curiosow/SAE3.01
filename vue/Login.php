<?php
include_once "../controleur/UserControleur.php";
include_once "../controleur/NotificationControleur.php";
include_once "../modele/managers/CollegueManager.php";

$controleur = new UserControleur();
$notificationControleur = new NotificationControleur();

session_start();

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
<body class="bg-gray-100 flex items-center justify-center min-h-screen px-4 sm:px-6 lg:px-8">

<main class="w-full max-w-md sm:max-w-lg lg:max-w-xl mx-auto text-center">

    <img class="mx-auto h-12 w-auto" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT7_hvZN3QSpzxIuVu8EPWSRpIZD-Al7AKMIQ&s" alt="Your Company">
    <h2 class="mt-6 text-lg sm:text-xl lg:text-2xl font-bold leading-9 text-gray-900">Connectez-vous avec votre mail UPHF</h2>

    <form class="space-y-6 mt-6" action="Login.php" method="POST">
        <div class="space-y-4">
            <div>
                <label for="email-address" class="sr-only">Adresse mail UPHF</label>
                <input id="email-address" name="email-address" type="email" autocomplete="email" required
                       class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 text-gray-900 focus:ring-indigo-600 focus:border-indigo-600 sm:text-base lg:text-lg"
                       placeholder="Adresse mail UPHF">
            </div>
            <div>
                <label for="password" class="sr-only">Mot de passe</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 text-gray-900 focus:ring-indigo-600 focus:border-indigo-600 sm:text-base lg:text-lg"
                       placeholder="Mot de passe">
            </div>
        </div>

        <button type="submit" class="w-full rounded-md bg-gray-600 px-4 py-2 text-white font-semibold hover:bg-gray-500 sm:text-lg lg:text-xl">
            Se connecter
        </button>
    </form>

    <div class="text-sm text-gray-500 space-y-2 sm:text-base lg:text-lg mt-4">
        <p>Vous avez oublié votre mot de passe ?
            <a href="ForgotPassword.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Changez-le ici</a>
        </p>
        <p>Vous n'avez pas de compte ?
            <a href="Register.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Enregistrez-vous ici</a>
        </p>
        <p>Vous n'êtes pas enregistré ?
            <a href="OldLogin.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Cherchez votre EDT</a>
        </p>
    </div>

</main>

</body>
</html>
