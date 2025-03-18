<?php
global $currentColors;
include_once "../controleur/UserControleur.php";
include_once "../modele/managers/CollegueManager.php";
include_once("theme.php");

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
<body class="<?php echo $currentColors['subbg'] ?> flex items-center justify-center min-h-screen px-4 sm:px-6 lg:px-8">

<div class="absolute top-0 right-0 p-4">
    <!-- Bouton pour basculer le thème -->
    <form action="theme.php" method="POST" class="flex items-center">
        <input type="hidden" name="current_file" value="Register.php">
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
    <h2 class="text-2xl font-bold leading-9 tracking-tight <?php echo $currentColors['text']; ?>">Enregistrez-vous avec votre mail UPHF</h2>

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
        Vous avez déjà un compte ?
        <a href="Login.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Connectez-vous ici</a>
    </p>

</main>

</body>
</html>