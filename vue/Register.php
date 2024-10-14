<?php
include "../modele/UsersManager.php";

session_start();

if(isset($_SESSION['logged'])) {
    header('location: Dashboard.php');
    exit();
}

if(isset($_POST['email-address']) && isset($_POST['password'])) {
    $mail = $_POST['email-address'];
    $password = $_POST['password'];

    $mail_err = $password_err = "";

    if(!hasAccount(trim($mail))) {
        if(!preg_match('/^[a-zA-Z0-9_@.]+$/', trim($_POST["email-address"]))) {
            $mail_err = "Le mail peut contenir uniquement des lettres, nombres et tirets.";
        } elseif (strpos($mail, '@uphf.fr') === false) {
            $mail_err = "Le mail doit appartenir au domaine 'uphf.fr'.";
        } else if (!emailExistsInCSV($mail)) {
            $mail_err = "Votre mail n'est pas encore enregistré.";
        }
    } else {
        $mail_err = "Le mail est déjà utilisé.";
    }

    if(strlen(trim($_POST["password"])) < 6) {
        $password_err = "Le mot de passe doit contenir au moins 6 caractères.";
    }

    if(empty($mail_err) && empty($password_err)) {
        registerUser($mail, password_hash($password, PASSWORD_DEFAULT));
        $_SESSION["logged"] = true;
        $_SESSION["mail"] = $mail;
        $_SESSION["role"] = 'ELEVE';

        $line = getLineFromCSVByEmail($mail);
        switch ($line[3]) {
            case '1':
            case '2':
                $_SESSION['promotion'] = '1';
                break;
            case '3':
            case '4':
                $_SESSION['promotion'] = '2';
                break;
            case '5':
            case '6':
                $_SESSION['promotion'] = '3';
                break;
        }

        $formation = 'FI';
        if(str_starts_with($line[4], 'FA'))
            $formation = 'FA';
        $_SESSION['formation'] = $formation;

        $group = removePrefix($line[4]);
        $_SESSION['groupe'] = $group[0];
        $_SESSION['sousgroupe'] = $group[1];

        $_SESSION['nom'] = $line[1];
        $_SESSION['prenom'] = $line[2];
        $_SESSION['civilite'] = $line[0];

        header('location: Dashboard.php');
        exit();
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

function emailExistsInCSV($email) {
    $file = fopen('../liste_groupes.csv', 'r');
    if ($file !== false) {
        while (($line = fgetcsv($file, 1000, ';')) !== false) {
            if (isset($line[5]) && strcasecmp(trim($line[5]), $email) === 0) {
                fclose($file);
                return true;
            }
        }
        fclose($file);
    }
    return false;
}

function getLineFromCSVByEmail($email) {
    $file = fopen('liste_groupes.csv', 'r');
    if ($file !== false) {
        while (($line = fgetcsv($file, 1000, ';')) !== false) {
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
</head>
<body>

<div class="flex min-h-full items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-sm space-y-10">

        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <img class="mx-auto h-10 w-auto" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT7_hvZN3QSpzxIuVu8EPWSRpIZD-Al7AKMIQ&s" alt="Your Company">
            <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Enregistrez-vous avec votre mail UPHF</h2>
        </div>

        <?php
        if(!empty($mail_err)) {
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
                              <li>'. $mail_err . '</li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>';
        }
        if(!empty($password_err)) {
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
                              <li>'. $password_err . '</li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>';
        }
        ?>

        <form class="space-y-6" action="Register.php" method="POST">
            <div class="relative -space-y-px rounded-md shadow-sm">
                <div class="pointer-events-none absolute inset-0 z-10 rounded-md ring-1 ring-inset ring-gray-300"></div>
                <div>
                    <label for="email-address" class="sr-only">Adresse mail UPHF</label>
                    <input id="email-address" name="email-address" type="email" autocomplete="email" required class="relative block w-full rounded-t-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-100 placeholder:text-gray-400 focus:z-10 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Adresse mail UPHF">
                </div>
                <div>
                    <label for="password" class="sr-only">Mot de passe</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="relative block w-full rounded-b-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-100 placeholder:text-gray-400 focus:z-10 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Mot de passe">
                </div>
            </div>

            <div>
                <button type="submit" class="flex w-full justify-center rounded-md bg-gray-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">S'enregistrer</button>
            </div>
        </form>

        <p class="text-center text-sm leading-6 text-gray-500">
            Vous avez déjà un compte ?
            <a href="Login.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Connectez-vous ici</a>
        </p>

    </div>
</div>

</body>
</html>