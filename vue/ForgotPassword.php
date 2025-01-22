<?php
include "../controleur/UserControleur.php";

$controleur = new UserControleur();

session_start();

if(isset($_COOKIE['logged']) && $_COOKIE['logged'] != "NONE") {
    header('location: Dashboard.php');
    exit();
}

if(isset($_POST['password'])) {
    if(strlen(trim($_POST["password"])) < 6) {
        $password_err = "Le mot de passe doit contenir au moins 6 caractères.";
    }

    if(!isset($password_err)) {
        $controleur->updateAccountPassword($_POST['token'], password_hash(trim($_POST["password"]), PASSWORD_DEFAULT));
        session_destroy();
        header('location: Login.php');
        exit();
    }
}

if(isset($_POST['email-address'])) {
    $mail = trim($_POST['email-address']);
    if($controleur->hasAccount(trim($mail))) {
        $_SESSION['mail'] = $mail;

        $token = bin2hex(random_bytes(50));
        $controleur->setAccountForgotToken($mail, $token);

        $api_key = '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';
        $line = getLineFromCSVByEmail($mail);
        $nom = $line[1];
        $prenom = $line[2];

        $subject = 'Récupérez votre compte';
        $message = "Bonjour $nom $prenom,\n\nVoici votre token de récupération : " . $token;

        $data = array(
            'sender' => array('name' => 'IUT Maubeuge - Emploi du temps', 'email' => 'bouttieroscar@gmail.com'),
            'to' => array(array('email' => $mail)),
            'subject' => $subject,
            'textContent' => $message
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.sendinblue.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'api-key: ' . $api_key,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            header('location: Error.php');
            exit();
        }
        curl_close($ch);

        $_SESSION['sended'] = true;
        $_SESSION['token'] = $token;
        header('location: ForgotPassword.php');
        exit();
    }
}

if(isset($_SESSION['sended'])) {
    $_SESSION['sended'] = false;
    $alert = "Si un compte est rattaché à ce mail, un mail avec votre token a été envoyé.";
}

if(isset($_SESSION['token'])) {
    $hasAToken = true;
} else {
    $hasAToken = false;
}

function getLineFromCSVByEmail($email) {
    $file = fopen('../liste_groupes.csv', 'r');
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
            <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Récupérez votre compte avec votre mail UPHF</h2>
        </div>

        <?php

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

        if(!empty($alert)) {
            echo '<div class="rounded-md bg-green-50 p-4">
                      <div class="flex">
                        <div class="flex-shrink-0">
                          <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                          </svg>
                        </div>
                        <div class="ml-3">
                          <h3 class="text-sm font-medium text-green-800">Action effectuée avec succès !</h3>
                          <div class="mt-2 text-sm text-green-700">
                            <ul role="list" class="list-disc space-y-1 pl-5">
                              <li>' . $alert . '</li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>';
        }

        if($hasAToken) {

            echo '<form class="space-y-6" action="ForgotPassword.php" method="POST">
            <div class="relative -space-y-px rounded-md shadow-sm">
                <div class="pointer-events-none absolute inset-0 z-10 rounded-md ring-1 ring-inset ring-gray-300"></div>
                <div>
                    <label for="token" class="sr-only">Token de validation</label>
                    <input id="token" name="token" type="password" autocomplete="current-password" required class="relative block w-full rounded-b-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-100 placeholder:text-gray-400 focus:z-10 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Token de validation">
                </div>
                <div>
                    <label for="password" class="sr-only">Mot de passe</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="relative block w-full rounded-b-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-100 placeholder:text-gray-400 focus:z-10 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Mot de passe">
                </div>
            </div>

            <div>
                <button type="submit" class="flex w-full justify-center rounded-md bg-gray-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">Changer le mot de passe</button>
            </div>
        </form>';

        } else if(empty($alert)) {

            echo '<form class="space-y-6" action="ForgotPassword.php" method="POST">
            <div class="relative -space-y-px rounded-md shadow-sm">
                <div class="pointer-events-none absolute inset-0 z-10 rounded-md ring-1 ring-inset ring-gray-300"></div>
                <div>
                    <label for="email-address" class="sr-only">Adresse mail UPHF</label>
                    <input id="email-address" name="email-address" type="email" autocomplete="email" required class="relative block w-full rounded-t-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-100 placeholder:text-gray-400 focus:z-10 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Adresse mail UPHF">
                </div>
            </div>

            <div>
                <button type="submit" class="flex w-full justify-center rounded-md bg-gray-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">Envoyer un mail de confirmation.</button>
            </div>
        </form>';

        }

        ?>
        <p class="text-center text-sm leading-6 text-gray-500">
            Vous avez retrouvé votre compte ?
            <a href="Login.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Connectez-vous ici</a>
        </p>
    </div>
</div>
</body>
</html>