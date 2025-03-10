<?php
include_once "../controleur/UserControleur.php";
include_once "../modele/managers/CollegueManager.php";

$controleur = new UserControleur();

session_start();

if(isset($_COOKIE['logged']) && $_COOKIE['logged'] != "NONE") {
    header('location: Dashboard.php');
    exit();
}

if(isset($_POST['email-address']) && isset($_POST['password'])) {
    $mail = $_POST['email-address'];
    $password = $_POST['password'];

    $mail_err = $password_err = "";

    if(!$controleur->hasAccount(trim($mail))) {
        if(!preg_match('/^[a-zA-Z0-9_@.]+$/', trim($_POST["email-address"]))) {
            $mail_err = "Le mail peut contenir uniquement des lettres, nombres et tirets.";
        } elseif (strpos($mail, '@uphf.fr') === false) {
            $mail_err = "Le mail doit appartenir au domaine 'uphf.fr'.";
        } else if (!emailExistsInCSV($mail) && !isACollegue($mail)) {
            $mail_err = "Votre mail n'est pas encore enregistré.";
        }
    } else {
        $mail_err = "Le mail est déjà utilisé.";
    }

    if(strlen(trim($_POST["password"])) < 6) {
        $password_err = "Le mot de passe doit contenir au moins 6 caractères.";
    }

    if(empty($mail_err) && empty($password_err)) {
        $token = bin2hex(random_bytes(50));

        $controleur->registerUser($mail, password_hash($password, PASSWORD_DEFAULT), $token);
        $api_key = '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';
        $line = getLineFromCSVByEmail($mail);
        $nom = $line[1];
        $prenom = $line[2];

        $subject = 'Confirmez votre adresse e-mail';
        $message = "Bonjour $nom $prenom,\n\nCliquez sur ce lien pour confirmer votre inscription : https://edt.curiosow.fr/vue/Confirm.php?token=" . $token;

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

        $_SESSION['just_register'] = true;
        header('location: Login.php');
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
<body class="bg-white flex items-center justify-center min-h-screen px-4 sm:px-6 lg:px-8">

<main class="w-full max-w-sm mx-auto text-center">

    <img class="mx-auto h-10 w-auto" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT7_hvZN3QSpzxIuVu8EPWSRpIZD-Al7AKMIQ&s">
    <h2 class="text-2xl font-bold leading-9 tracking-tight text-gray-900">Enregistrez-vous avec votre mail UPHF</h2>

    <?php
    if(!empty($mail_err)) {
        echo '<div class="rounded-md bg-red-50 p-4 mt-4 text-left">
                  <div class="flex">
                    <svg class="h-5 w-5 text-red-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                      <h3 class="text-sm font-medium text-red-800">Une erreur est survenue.</h3>
                      <ul class="list-disc space-y-1 pl-5 text-sm text-red-700">
                          <li>'. $mail_err . '</li>
                      </ul>
                    </div>
                  </div>
              </div>';
    }
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
    ?>

    <form class="space-y-6 mt-6" action="Register.php" method="POST">
        <div class="space-y-4">
            <div>
                <label for="email-address" class="sr-only">Adresse mail UPHF</label>
                <input id="email-address" name="email-address" type="email" autocomplete="email" required
                       class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 text-gray-900 focus:ring-indigo-600 focus:border-indigo-600 sm:text-base"
                       placeholder="Adresse mail UPHF">
            </div>
            <div>
                <label for="password" class="sr-only">Mot de passe</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       class="block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 text-gray-900 focus:ring-indigo-600 focus:border-indigo-600 sm:text-base"
                       placeholder="Mot de passe">
            </div>
        </div>

        <button type="submit" class="w-full rounded-md bg-gray-600 px-4 py-2 text-white font-semibold hover:bg-gray-500 sm:text-lg">
            S'enregistrer
        </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-4">
        Vous avez déjà un compte ?
        <a href="Login.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Connectez-vous ici</a>
    </p>

</main>

</body>
</html>