<?php
include_once("../controleur/Controleur.php");
include_once("../controleur/NotificationControleur.php");

session_start();

$controleur = new Controleur();
$notificationsControleur = new NotificationControleur();

// Vérification si l'utilisateur n'est pas connecté
if(!isset($_SESSION['groupe'])) {
    header('location: Login.php');
    exit();
}

// Vérification si l'utilisateur souhaite se déconnecter
if(isset($_POST['disconnect'])) {
    session_destroy();
    header('location: Login.php');
    exit();
}

// Vérification si l'utilisateur souhaite soumettre une absence (pour les profs & gestionnaires)
if(isset($_POST['absence'])) {
    $id = $_SESSION['mail'];
    if(isset($_SESSION['collegue']))
        $id = $_SESSION['collegue'];

    $start_date = DateTime::createFromFormat('d-m-Y H:i', $_POST['start-date'])->format('d-m-Y H:i');
    $end_date = DateTime::createFromFormat('d-m-Y H:i', $_POST['end-date'])->format('d-m-Y H:i');

    $notificationsControleur->createNotification("Demande de changement d'emploi du temps", $id . " ne sera pas présent du " . $start_date . " jusqu'au " . $end_date . " pour le motif : " . $_POST['reason'] . ".", "GESTIONNAIRE", true);
}

// Données de bases
setlocale(LC_TIME, 'fr_FR.UTF-8');
$realDate = new DateTime('now', new DateTimeZone('Europe/Paris'));
$date = clone $realDate;
$week = clone $realDate;

// Vérification si l'utilisateur à demander de changer de mois
if (isset($_POST['monthOffSet'])) {
    $_SESSION['monthOffSet'] = (int)$_POST['monthOffSet'];
} else {
    // Si ce n'est pas le cas, si aucun mois n'est enregistrer dans la session, alors on définit au mois actuel.
    if (!isset($_SESSION['monthOffSet'])) {
        $_SESSION['monthOffSet'] = 0;
    }
}

// Vérification si l'utilisateur à demander de changer de semaine
if (isset($_POST['weekOffSet'])) {
    $_SESSION['weekOffSet'] = (int)$_POST['weekOffSet'];
} else {
    // Si ce n'est pas le cas, si aucune semaine n'est enregistrer dans la session, alors on définit à la semaine actuelle.
    if (!isset($_SESSION['weekOffSet'])) {
        $_SESSION['weekOffSet'] = 0;
    }
}

// Modification des données par rapport à l'utilisateur
$date = $date->modify($_SESSION['monthOffSet'] . ' month');
$week = $week->modify(($_SESSION['weekOffSet'] * 7) . ' days');
$month = IntlDateFormatter::formatObject($date, 'MMMM y', 'fr');

function generateCalendar() {
    global $date, $realDate;
    $month = $date->format('m');
    $year = $date->format('Y');

    // Trouver le premier et le dernier jour du mois
    $firstDayOfMonth = date('Y-m-01', strtotime("$year-$month-01"));
    $lastDayOfMonth = date('Y-m-t', strtotime($firstDayOfMonth));

    // Trouver le jour de la semaine du premier et du dernier jour du mois
    $startDayOfWeek = date('N', strtotime($firstDayOfMonth));
    $endDayOfWeek = date('N', strtotime($lastDayOfMonth));

    $startDate = date('Y-m-d', strtotime($firstDayOfMonth . ' -' . ($startDayOfWeek - 1) . ' days'));
    $endDate = date('Y-m-d', strtotime($lastDayOfMonth . ' +' . (7 - $endDayOfWeek) . ' days'));

    $currentDate = $startDate;
    $calendar = [];

    while ($currentDate <= $endDate) {
        $calendar[] = $currentDate;
        $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
    }

    $actualDay = clone $realDate;
    $actualDay = $actualDay->format('Y-m-d');
    foreach ($calendar as $d) {
        $day = date('d', strtotime($d));
        $cMonth = date('m', strtotime($d));

        $buttonClass = 'rounded-tl-lg bg-black-50 py-1.5 text-white focus:z-10';
        if ($d == $actualDay) {
            $buttonClass = 'rounded-full border-2 border-sky-700 bg-black-50 py-1.5 text-white focus:z-10';
        } elseif ($cMonth != $month) {
            $buttonClass = 'rounded-tl-lg bg-black-50 py-1.5 text-gray-600 focus:z-10';
        }

        echo '<button type="button" class="' . $buttonClass . '">
            <time class="mx-auto flex h-7 w-7 items-center justify-center rounded-full">' . $day . '</time>
        </button>';
    }
}

function removeAfterTiret($string) {
    $parts = explode(' - ', $string);
    return $parts[0];
}

function transformTeacherName($fullName) {
    $parts = explode(' ', $fullName);
    if (count($parts) < 2) {
        return $fullName;
    }
    $initial = substr($parts[0], 0, 1) . '.';
    $lastName = $parts[1];
    return $initial . ' ' . $lastName;
}

function getWeekDates(DateTime $date) {
    $startOfWeek = clone $date;
    $endOfWeek = clone $date;

    $startOfWeek->modify('monday this week');
    $endOfWeek->modify('sunday this week');

    $weekDates = [];

    $currentDate = clone $startOfWeek;
    while ($currentDate <= $endOfWeek) {
        $weekDates[] = clone $currentDate;
        $currentDate->modify('+1 day');
    }

    return $weekDates;
}

function getGridRow(DateTime $dateTime) {
    $hour = (int) $dateTime->format('H');
    $minute = (int) $dateTime->format('i');

    $gridRow = (($hour - 7) * 2);
    if ($minute >= 30) {
        $gridRow += 1;
    }
    return $gridRow;
}


function getSpan(DateTime $duration) {
    $hours = (int) $duration->format('H');
    $minutes = (int) $duration->format('i');

    $span = ($hours * 2);
    if ($minutes >= 30) {
        $span += 1;
    }

    return $span;
}

//La fonction getDayWeek renvoie la date d'un jour spécifique de la semaine en cours. Elle prend un jour en paramètre et modifie la date de la semaine clonée pour correspondre au jour spécifié
function getDayWeek($day) {
    global $week;
    $resultDate = clone $week;
    $resultDate->modify($day . ' this week');

    return $resultDate;
}

//La fonction getWeekDay renvoie la date soit du lundi, soit du dimanche de la semaine en cours, en fonction du paramètre firstDay.
function getWeekDay($firstDay) {
    global $week;
    $resultDate = clone $week;

    if ($firstDay) {
        $resultDate->modify('monday this week');
    } else {
        $resultDate->modify('sunday this week');
    }

    return $resultDate;
}

// Récupération de la version la plus récente
$version = $controleur->returnVersion();

// On inscrit ici le role de l'utilisateur pour le récupérer depuis JS
$role = 'ELEVE';
if(isset($_SESSION['role']))
    $role = $_SESSION['role'];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emploi du temps</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // cette fonction permet de rétracter ou d'étendre la sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('dashboard');
            sidebar.classList.toggle('lg:w-72');
            sidebar.classList.toggle('lg:w-16');
            mainContent.classList.toggle('lg:pl-72');
            mainContent.classList.toggle('lg:pl-16');
            const elementsToHide = document.querySelectorAll('.hide-when-collapsed');
            elementsToHide.forEach(element => {
                element.classList.toggle('hidden');
            });
        }
    </script>
</head>
<body>

<!--section pour afficher la version -->
<div class="absolute top-0 left-72 p-4">
    <span class="text-xs text-gray-400">Version: <?php echo $version; if(isset($_SESSION['role']) && $_SESSION['role'] != 'ELEVE') { echo ' - Vous êtes ' . $_SESSION['role']; } ?></span>
</div>

<!-- cloche Icon -->
<!-- Notification Sidebar -->
<?php
if (isset($_SESSION['logged'])) {
    $notifications = $notificationsControleur->getUnreadNotifications();

    echo '
    <div class="absolute top-0 right-0 p-4">
        <div class="relative">
            <button onclick="toggleNotificationSidebar()" class="text-black focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="black" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </button>
            <!-- Notification Bulles -->
            ';
    if (sizeof($notifications) != 0)
        echo '<span class="absolute top-0 right-0 inline-flex h-2 w-2 rounded-full bg-red-600"></span>';
    echo '</div></div>';

    echo '<div id="notificationSidebar" class="fixed inset-y-0 right-0 z-50 w-64 bg-white shadow-lg transform translate-x-full transition-transform duration-300">
        <div class="p-4 max-h-full overflow-y-scroll">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold">Notifications</h2>
                <button onclick="toggleNotificationSidebar()" class="text-black focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="black" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <ul id="notificationList" class="mt-4 space-y-2">
            ';

    $notifications = array_reverse($notifications);
    foreach ($notifications as $notification) {
        echo '<li class="p-2 bg-gray-100 rounded-md">
            <h3 class="font-semibold">' . $notification->getTitle() . '</h3>
            <p class="text-sm">' . $notification->getContent() . '</p>
            </li>';
    }

    echo '</ul>
        </div>
        <div class="absolute bottom-0 p-4 pl-12 pr-12 bg-white">
            <button id="showAllNotifications" class="rounded bg-white-800 px-2 py-1 text-xs font-semibold text--300 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-100" onclick="toggleAllNotificationsLayer()">Afficher toutes les notifications</button>
        </div>
    </div>
    ';
}
?>


<!-- All Notifications Layer -->
<div id="allNotificationsLayer" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-3/4 max-w-3xl max-h-[44rem] overflow-y-scroll">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Toutes les Notifications</h2>
            <button onclick="toggleAllNotificationsLayer()" class="text-black focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="black" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <ul id="allNotificationList" class="mt-4 space-y-2">
            <?php
            if(isset($_SESSION['logged'])) {
                $allNotifications = $notificationsControleur->getAllNotifications();
                $allNotifications = array_reverse($allNotifications);
                foreach ($allNotifications as $notification) {
                    echo '<li class="p-2 bg-gray-100 rounded-md">
                    <h3 class="font-semibold">' . $notification->getTitle() . '</h3>
                    <p class="text-sm">' . $notification->getContent() . '</p>
                    </li>';
                }
            }
            ?>
        </ul>
    </div>
</div>


<script>
    // Fonction pour bascule la visibilité de l'élément ayant l'ID allNotificationsLayer en ajoutant ou en supprimant la classe hidden.
    function toggleAllNotificationsLayer() {
        const layer = document.getElementById('allNotificationsLayer');
        layer.classList.toggle('hidden');
    }
</script>


<script>
    // Fonction pour afficher la sidebar des notifications
    function toggleNotificationSidebar() {
        const sidebar = document.getElementById('notificationSidebar');
        sidebar.classList.toggle('translate-x-full');
    }
</script>



<!-- Bouton pour signaler une absence -->
<div class="absolute top-0 right-0 p-4 flex items-center space-x-2">
    <div class="relative">
        <button onclick="toggleAbsencePopup()" class="text-black focus:outline-none">
            <svg class="h-6 w-6" fill="#000000" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <path d="M21.9,37c0-2.7,0.9-5.8,2.3-8.2c1.7-3,3.6-4.2,5.1-6.4c2.5-3.7,3-9,1.4-13c-1.6-4.1-5.4-6.5-9.8-6.4 s-8,2.8-9.4,6.9c-1.6,4.5-0.9,9.9,2.7,13.3c1.5,1.4,2.9,3.6,2.1,5.7c-0.7,2-3.1,2.9-4.8,3.7c-3.9,1.7-8.6,4.1-9.4,8.7 C1.3,45.1,3.9,49,8,49h17c0.8,0,1.3-1,0.8-1.6C23.3,44.5,21.9,40.8,21.9,37z"></path>
                    <path d="M46.4,28.5c-4.7-4.7-12.3-4.7-17,0c-4.7,4.7-4.7,12.3,0,17c4.7,4.7,12.3,4.7,17,0 C51.1,40.8,51.1,33.2,46.4,28.5z M42.9,39.1c0.4,0.4,0.4,1.1-0.1,1.5l-1.4,1.4c-0.4,0.4-0.9,0.4-1.3-0.1L38,39.8l-2.2,2.2 c-0.4,0.4-0.9,0.4-1.3-0.1L33,40.5c-0.4-0.4-0.5-0.9-0.1-1.3l2.2-2.2L33,34.8c-0.4-0.4-0.5-0.9-0.1-1.3l1.4-1.4 c0.4-0.4,1.1-0.5,1.5-0.1l2.1,2.1l2.1-2.1c0.4-0.4,1.1-0.5,1.5-0.1l1.4,1.4c0.4,0.4,0.4,1.1-0.1,1.5l-2.1,2.1L42.9,39.1z"></path>
                </g>
            </svg>
        </button>
    </div>
    <div class="relative">
        <button onclick="toggleNotificationSidebar()" class="text-black focus:outline-none">
            <svg class="h-6 w-6" fill="none" stroke="black" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </button>
    </div>
</div>

<!-- Pop-up pour signaler une absence -->
<div id="absencePopup" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-3/4 max-w-3xl">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Signaler une absence</h2>
            <button onclick="toggleAbsencePopup()" class="text-black focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="black" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form action="Dashboard.php" method="POST">
            <div class="mb-4">
                <label for="start-date" class="block text-sm font-medium text-gray-700">Date de début</label>
                <input type="date" id="start-date" name="start-date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <script>
                    flatpickr("#start-date", {
                        enableTime: true,
                        dateFormat: "d-m-Y H:i",
                        time_24hr: true,
                        minuteIncrement: 30,
                        minTime: "08:00",
                        maxTime: "17:00"
                    });
                </script>
            </div>
            <div class="mb-4">
                <label for="end-date" class="block text-sm font-medium text-gray-700">Date de fin</label>
                <input type="date" id="end-date" name="end-date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <script>
                    flatpickr("#end-date", {
                        enableTime: true,
                        dateFormat: "d-m-Y H:i",
                        time_24hr: true,
                        minuteIncrement: 30,
                        minTime: "08:00",
                        maxTime: "17:00"
                    });
                </script>
            </div>
            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-gray-700">Motif</label>
                <textarea id="reason" name="reason" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" id="absence" name="absence" class="rounded-md bg-gray-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">Soumettre</button>
            </div>
        </form>
    </div>
</div>

<script>
    let clickedDate = null;
    // Fonction qui vérifie le rôle de l'utilisateur et bascule la visibilité de la fenêtre popup d'absence si le rôle n'est pas un élève
    function toggleAbsencePopup() {
        let role = <?php echo '"'. $role . '"'; ?>;
        if (role == null || role === 'ELEVE') {
            return;
        }

        const popup = document.getElementById('absencePopup');
        popup.classList.toggle('hidden');

        if (clickedDate) {
            flatpickr("#start-date", {
                enableTime: true,
                dateFormat: "d-m-Y H:i",
                time_24hr: true,
                minuteIncrement: 30,
                minTime: "08:00",
                maxTime: "17:00",
                defaultDate: clickedDate
            });
        }
    }
    // Fonction qui gère l'événement lorsqu'un jour est cliqué dans le calendrier
    function handleDayClicked(day) {
        clickedDate = day.getAttribute('data-date');
        clickedDate = new Date(clickedDate);
        clickedDate.setHours(8);
        toggleAbsencePopup();
    }
</script>

<!-- Static sidebar -->
<div id="sidebar" class="lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col transition-width duration-300">
    <!-- Bouton pour rétracter/étendre la sidebar -->
    <div class="absolute top-0 right-0 p-2">
        <button onclick="toggleSidebar()" class="text-white focus:outline-none">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>
    </div>
    <!-- Sidebar component-->
    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-black px-6 pb-4 pt-10">
        <!-- Calendrier -->
        <div class="mt-10 text-center lg:col-start-8 lg:col-end-13 lg:row-start-1 lg:mt-9 xl:col-start-9 hide-when-collapsed">
            <!-- Boutons mois précédents/suivants -->
            <div class="flex items-center text-gray-400">
                <form action="Dashboard.php" method="post" class="flex w-full">
                    <button type="submit" name="monthOffSet" value="<?php echo ($_SESSION['monthOffSet'] - 1); ?>" class="-m-1.5 flex flex-none items-center justify-center p-1.5 text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Mois précédent</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div class="flex-auto text-sm font-semibold"><?php echo ucfirst($month); ?></div>
                    <button type="submit" name="monthOffSet" value="<?php echo ($_SESSION['monthOffSet'] + 1); ?>" class="-m-1.5 flex flex-none items-center justify-center p-1.5 text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Mois suivant</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Affichage calendrier -->
            <div class="mt-6 grid grid-cols-7 text-xs leading-6 text-gray-400">
                <div>Lun</div><div>Mar</div><div>Mer</div><div>Jeu</div><div>Ven</div><div>Sam</div><div>Dim</div>
            </div>
            <div class="isolate mt-2 grid grid-cols-7 gap-px rounded-lg bg-black text-sm shadow ring-1 ring-black">
                <?php generateCalendar(); ?>
            </div>
        </div>

        <!-- Sidebar footer -->
        <div class="mt-auto flex-col justify-center hide-when-collapsed">
            <form action="Dashboard.php" method="POST" class="mb-4 flex justify-center">
                <button type="submit" id="disconnect" name="disconnect" class="rounded bg-gray-800 px-2 py-1 text-xs font-semibold text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-900">Se déconnecter</button>
            </form>
            <svg xmlns="http://www.w3.org/2000/svg" width="240" height="1" viewBox="0 0 240 1" fill="none">
                <path d="M0 0.5H240" stroke="#898888"/>
            </svg>
            <div class="mt-4 flex justify-center gap-12">
                <img class="grayscale opacity-65" src="img/ics.png" alt="Image 1">
                <img class="grayscale opacity-65" src="img/pdf.png" alt="Image 2">
            </div>
        </div>
    </div>
</div>

<!--Dashboard-->
<div id="dashboard" class="lg:pl-72 transition-all duration-300">
    <div class="flex h-full flex-col">
        <!-- topbar (changeur de semaines) -->
        <header class="flex justify-center items-center border-b border-gray-200 px-4 py-2">
            <form action="Dashboard.php" method="POST">
                <div class="flex flex-center items-center rounded-md bg-white shadow-sm md:items-stretch">
                    <button type="submit" name="weekOffSet" value="<?php echo ($_SESSION['weekOffSet'] - 1); ?>" class="flex h-9 w-12 items-center justify-center rounded-l-md border-y border-l border-gray-300 pr-1 text-gray-400 hover:text-gray-500 focus:relative md:w-9 md:pr-0 md:hover:bg-gray-50">
                        <span class="sr-only">Semaine précédente</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button type="submit" name="weekOffSet" value="0" class="hidden border-y border-gray-300 px-3.5 text-sm font-semibold text-gray-900 hover:bg-gray-50 focus:relative md:block">Du <?php $fDay = getWeekDay(true); echo $fDay->format('d M') ?> au <?php $lDay = getWeekDay(false); echo $lDay->format('d M') ?></button>
                    <span class="relative -mx-px h-5 w-px bg-gray-300 md:hidden"></span>
                    <button type="submit" name="weekOffSet" value="<?php echo ($_SESSION['weekOffSet'] + 1); ?>" class="flex h-9 w-12 items-center justify-center rounded-r-md border-y border-r border-gray-300 pl-1 text-gray-400 hover:text-gray-500 focus:relative md:w-9 md:pl-0 md:hover:bg-gray-50">
                        <span class="sr-only">Semaine suivante</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </form>
        </header>

        <div class="isolate flex flex-auto flex-col overflow-auto bg-white">
            <div class="flex max-w-full flex-none flex-col sm:max-w-none md:max-w-full">
                <div class="sticky top-0 z-30 flex-none bg-white shadow ring-1 ring-black ring-opacity-5 sm:pr-8">
                    <div class="-mr-px hidden grid-cols-5 divide-x divide-gray-100 border-r border-gray-100 text-sm leading-6 text-gray-500 sm:grid">
                        <div class="col-end-1 w-14"></div>
                        <!-- affichage de la semaine -->
                        <div class="flex items-center justify-center py-3 hover:bg-gray-200" data-date="<?php $thisDay = getDayWeek('monday'); echo $thisDay->format('Y-m-d') ?> " onclick="handleDayClicked(this)">
                            <span>Lun <span class="items-center justify-center font-semibold text-gray-900"><?php $thisDay = getDayWeek('monday'); echo $thisDay->format('d M'); ?></span></span>
                        </div>
                        <div class="flex items-center justify-center py-3 hover:bg-gray-200" data-date="<?php $thisDay = getDayWeek('tuesday'); echo $thisDay->format('Y-m-d') ?> " onclick="handleDayClicked(this)">
                            <span>Mar <span class="items-center justify-center font-semibold text-gray-900"><?php $thisDay = getDayWeek('tuesday'); echo $thisDay->format('d M'); ?></span></span>
                        </div>
                        <div class="flex items-center justify-center py-3 hover:bg-gray-200" data-date="<?php $thisDay = getDayWeek('wednesday'); echo $thisDay->format('Y-m-d') ?> " onclick="handleDayClicked(this)">
                            <span>Mer <span class="items-center justify-center font-semibold text-gray-900"><?php $thisDay = getDayWeek('wednesday'); echo $thisDay->format('d M'); ?></span></span>
                        </div>
                        <div class="flex items-center justify-center py-3 hover:bg-gray-200" data-date="<?php $thisDay = getDayWeek('thursday'); echo $thisDay->format('Y-m-d') ?> " onclick="handleDayClicked(this)">
                            <span>Jeu <span class="items-center justify-center font-semibold text-gray-900"><?php $thisDay = getDayWeek('thursday'); echo $thisDay->format('d M'); ?></span></span>
                        </div>
                        <div class="flex items-center justify-center py-3 hover:bg-gray-200" data-date="<?php $thisDay = getDayWeek('friday'); echo $thisDay->format('Y-m-d') ?> " onclick="handleDayClicked(this)">
                            <span>Ven <span class="items-center justify-center font-semibold text-gray-900"><?php $thisDay = getDayWeek('friday'); echo $thisDay->format('d M'); ?></span></span>
                        </div>
                    </div>
                </div>

                <!-- Heures sur la gauche du calendrier -->
                <div class="flex flex-auto">
                    <div class="sticky left-0 z-10 w-14 flex-none bg-white ring-1 ring-gray-100"></div>
                    <div class="grid flex-auto grid-cols-1 grid-rows-1">
                        <!-- Vertical lines -->
                        <div class="col-start-1 col-end-2 row-start-1 grid-cols-5 grid-rows-1 divide-x divide-gray-200 sm:grid sm:grid-cols-5">
                            <div class="col-start-1 row-span-full"></div>
                            <div class="col-start-2 row-span-full"></div>
                            <div class="col-start-3 row-span-full"></div>
                            <div class="col-start-4 row-span-full"></div>
                            <div class="col-start-5 row-span-full"></div>
                            <div class="col-start-6 row-span-full w-8"></div>
                        </div>

                        <!-- Horizontal lines -->
                        <div class="col-start-1 col-end-2 row-start-1 grid divide-y divide-gray-100" style="grid-template-rows: repeat(19, minmax(4.2vh , 1fr))">
                            <!-- Adjust top margin for mobile -->
                            <div class="row-end-1 h-7"></div>
                            <?php
                            for ($i = 8; $i <= 17; $i++) {
                                echo '<div>
                                    <div class="sticky left-0 z-20 -ml-14 -mt-2.5 w-14 pr-2 text-right text-xs leading-5 text-gray-400">' . $i . '</div>
                                    </div>
                                    <div></div>';
                            }
                            ?>
                        </div>

                        <!-- Events -->
                        <ol class="col-start-1 col-end-2 row-start-1 grid grid-cols-1 sm:grid-cols-5 sm:pr-8"  style="grid-template-rows: 1.75rem repeat(19, minmax(4.2vh, 1fr)) auto">
                            <?php $controleur->generateDays($week); ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>