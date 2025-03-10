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
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen px-4 sm:px-6 lg:px-8">

<main class="w-full max-w-sm mx-auto text-center">
    <img class="mx-auto h-10 w-auto" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT7_hvZN3QSpzxIuVu8EPWSRpIZD-Al7AKMIQ&s" alt="Your Company">
    <h2 class="mt-10 text-2xl font-bold leading-9 tracking-tight text-gray-900">Récupérez votre compte avec votre mail UPHF</h2>

    <?php
    if(!empty($password_err)) {
        echo '<div class="rounded-md bg-red-50 p-4 mt-4 text-left">
                  <div class="flex">
                    <svg class="h-5 w-5 text-red-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                      <h3 class="text-sm font-medium text-red-800">Une erreur est survenue.</h3>
                      <ul class="list-disc space-y-1 pl-5 text-sm text-red-700">
                          <li>'. $password_err . '</li>
                      </ul>
                    </div>
                  </div>
              </div>';
    }

    if(!empty($alert)) {
        echo '<div class="rounded-md bg-green-50 p-4 mt-4 text-left">
                  <div class="flex">
                    <svg class="h-5 w-5 text-green-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                      <h3 class="text-sm font-medium text-green-800">Action effectuée avec succès !</h3>
                      <ul class="list-disc space-y-1 pl-5 text-sm text-green-700">
                          <li>' . $alert . '</li>
                      </ul>
                    </div>
                  </div>
              </div>';
    }
    ?>

    <form class="space-y-6 mt-6" action="ForgotPassword.php" method="POST">
        <?php if($hasAToken): ?>
            <input name="token" type="password" required class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 text-gray-900 focus:ring-indigo-600 focus:border-indigo-600 sm:text-base" placeholder="Token de validation">
            <input name="password" type="password" required class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 text-gray-900 focus:ring-indigo-600 focus:border-indigo-600 sm:text-base" placeholder="Nouveau mot de passe">
            <button type="submit" class="w-full rounded-md bg-gray-600 px-4 py-2 text-white font-semibold hover:bg-gray-500 sm:text-lg">Changer le mot de passe</button>
        <?php else: ?>
            <input name="email-address" type="email" required class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 text-gray-900 focus:ring-indigo-600 focus:border-indigo-600 sm:text-base" placeholder="Adresse mail UPHF">
            <button type="submit" class="w-full rounded-md bg-gray-600 px-4 py-2 text-white font-semibold hover:bg-gray-500 sm:text-lg">Envoyer un mail de confirmation</button>
        <?php endif; ?>
    </form>

    <p class="text-center text-sm text-gray-500 mt-4">
        Vous avez retrouvé votre compte ?
        <a href="Login.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Connectez-vous ici</a>
    </p>
</main>

</body>
</html>
