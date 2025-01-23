<?php
include_once "../controleur/UserControleur.php";
session_start();

$controleur = new UserControleur();
$resources = $controleur->getAllRessources();

if(isset($_COOKIE['groupe']) && $_COOKIE['groupe'] != "NONE") {
    header('location: Dashboard.php');
    exit();
}

if(isset($_POST['groupe'])) {
    foreach ($resources as $resource) {
        if ($resource['nomressource'] == $_POST['groupe']) {

            if (!isset($_COOKIE["semestre"]))
                setcookie("semestre", $resource['semestre'], time() + 30 * 24 * 60 * 60, "/");
            else
                setcookie("semestre", $resource['semestre']);

            if (!isset($_COOKIE["formation"]))
                setcookie("formation", $resource['typeformation'], time() + 30 * 24 * 60 * 60, "/");
            else
                setcookie("formation", $resource['typeformation']);

            $groupe = '';
            if ($resource['parcours'] == 'AB')
                $groupe = substr($resource['nomressource'], 0, 1);
            else
                $groupe = $resource['parcours'];

            if (!isset($_COOKIE["groupe"]))
                setcookie("groupe", $groupe, time() + 30 * 24 * 60 * 60, "/");
            else
                setcookie("groupe", $groupe);

            if (!isset($_COOKIE["sousgroupe"]))
                setcookie("sousgroupe", substr($resource['nomressource'], -1), time() + 30 * 24 * 60 * 60, "/");
            else
                setcookie("sousgroupe", substr($resource['nomressource'], -1));


            header('location: Dashboard.php');
            exit();
        }
    }
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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetch('getResources.php')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('groupe');
                    data.forEach(resource => {
                        const option = document.createElement('option');
                        option.value = resource.nomressource;
                        option.textContent = resource.nomressource;
                        select.appendChild(option);
                    });
                })
                .catch(error => console.error(error));
        });
    </script>
</head>
<body>
<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <img class="mx-auto h-10 w-auto" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT7_hvZN3QSpzxIuVu8EPWSRpIZD-Al7AKMIQ&s" alt="Your Company">
        <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Accédez via votre groupe</h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        <form class="space-y-6" action="OldLogin.php" method="POST">
            <div>
                <label for="groupe" class="block text-sm font-medium leading-6 text-gray-900">Groupe</label>
                <div class="mt-2">
                    <select id="groupe" name="groupe" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </select>
                </div>
            </div>
            <div>
                <button type="submit" class="flex w-full justify-center rounded-md bg-gray-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">Connexion</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>