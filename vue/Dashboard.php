<?php
global $currentColors;
include_once("../controleur/Controleur.php");
include_once("../controleur/NotificationControleur.php");
include_once("../controleur/UtilsControleur.php");
include_once("theme.php");

session_start();

$controleur = new Controleur();
$notificationsControleur = new NotificationControleur();

// Vérification si le thème n'est pas initialiser
if(!isset($_COOKIE['theme'])) {
    $_COOKIE['theme'] = 'light';
}

// Vérification si l'utilisateur n'est pas connecté
if(!isset($_COOKIE['groupe']) || $_COOKIE['groupe'] == "NONE") {
    header('location: Login.php');
    exit();
}

// Vérification si l'utilisateur souhaite se déconnecter
if(isset($_POST['disconnect'])) disconnect();

// Vérification si l'utilisateur souhaite soumettre une absence (pour les profs & gestionnaires)
if(isset($_POST['absence'])) createAbsence($notificationsControleur, $_POST['start-date'], $_POST['end-date'], $_POST['reason']);

// Vérification si l'utilisateur souhaite soumettre une notification de modification d'edt aux professeurs (pour les gestionnaires)
if(isset($_POST['gestio-ping-modification'])) notifNewVersion($notificationsControleur);

// Vérification si l'utilisateur souhaite changer de groupe (pour les gestionnaires)
if(isset($_POST['change-groupe'])) changeGroupe($_POST['newGroupe']);

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

// Si ce n'est pas le cas, si aucun jour n'est enregistrer dans la session, alors on définit au jour actuel.
if (!isset($_SESSION['dayOffSet'])) {
    $_SESSION['dayOffSet'] = 0;
}

// Modification des données par rapport à l'utilisateur
$date = $date->modify($_SESSION['monthOffSet'] . ' month');
$week = $week->modify(($_SESSION['weekOffSet'] * 7) . ' days');
$month = IntlDateFormatter::formatObject($date, 'MMMM y', 'fr');

// Vérification si l'utilisateur souhaite faire un pdf du mois actuel
if(isset($_POST['PDF'])) getCalendarPdf($week);

// Vérification si l'utilisateur souhaite faire un ical du mois actuel
if(isset($_POST['ICAL'])) getCalendarIcal($week);

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
$version = returnVersion();

// On inscrit ici le role de l'utilisateur pour le récupérer depuis JS
$role = 'ELEVE';
if(isset($_COOKIE['role']) && $_COOKIE['role'] != "NONE") {
    $role = $_COOKIE['role'];
    $notificationsControleur->setToLastNotification();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emploi du temps</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script
            type="module"
            src="https://unpkg.com/@material-tailwind/html@latest/scripts/tooltip.js">
    </script>
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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetch('getResources.php')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('newGroupe');
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipButtons = document.querySelectorAll('[data-tooltip-target]');

            tooltipButtons.forEach(button => {
                const tooltipId = button.getAttribute('data-tooltip-target');
                const tooltip = document.getElementById(tooltipId);

                button.addEventListener('mouseenter', () => {
                    tooltip.style.display = 'block';
                    tooltip.style.opacity = '1';

                    const rect = tooltip.getBoundingClientRect();
                    const viewportWidth = window.innerWidth;
                    const viewportHeight = window.innerHeight;

                    // Adjust position if tooltip goes off the right edge
                    if (rect.right > viewportWidth) {
                        tooltip.style.right = 'auto';
                        tooltip.style.left = '0';
                    }

                    // Adjust position if tooltip goes off the bottom edge
                    if (rect.bottom > viewportHeight) {
                        tooltip.style.top = 'auto';
                        tooltip.style.bottom = '100%';
                    }
                });

                button.addEventListener('mouseleave', () => {
                    tooltip.style.display = 'none';
                    tooltip.style.opacity = '0';
                });
            });
        });
    </script>
</head>
<body class="<?php echo $currentColors['bg']; ?> <?php echo $currentColors['text']; ?>">

<!--section pour afficher la version
<div class="absolute top-0 left-72 p-4">
</div> -->

<!-- cloche Icon -->
<!-- Notification Sidebar -->
<?php
if (isset($_COOKIE['logged']) && $_COOKIE['logged'] != "NONE") {
    $notifications = $notificationsControleur->getUnreadNotifications();

    echo '
    <div class="absolute top-0 right-0 p-4">
        <div class="relative">';
    if (sizeof($notifications) != 0)
        echo '<span class="absolute top-0 right-0 inline-flex h-2 w-2 rounded-full bg-red-600"></span>';
    echo '</div></div>';

    echo '<div id="notificationSidebar" class="fixed inset-y-0 right-0 z-50 w-64 shadow-lg transform translate-x-full transition-transform duration-300 '; echo $currentColors['bg']; echo '">
        <div class="p-4 max-h-full overflow-y-scroll">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold '; echo $currentColors['text']; echo '">Notifications</h2>
                <button onclick="toggleNotificationSidebar()" class="focus:outline-none'; echo $currentColors['text']; echo '">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <ul id="notificationList" class="mt-4 space-y-2">
            ';

    $notifications = array_reverse($notifications);
    foreach ($notifications as $notification) {
        echo '<li class="p-2 rounded-md '; echo $currentColors['bg']; echo '">
            <h3 class="font-semibold '; echo $currentColors['text']; echo '">' . $notification->getTitle() . '</h3>
            <p class="text-sm '; echo $currentColors['text']; echo '">' . $notification->getContent() . '</p>
            </li>';
    }

    echo '</ul>
        </div>
        <div class="absolute bottom-0 p-4 pl-12 pr-12 '; echo $currentColors['bg']; echo '">
            <button id="showAllNotifications" class="rounded px-2 py-1 text-xs font-semibold shadow-sm ring-1 ring-inset '; echo $currentColors['ring']; echo 'hover:'; echo $currentColors['hover']; echo '" onclick="toggleAllNotificationsLayer()">Afficher toutes les notifications</button>
        </div>
    </div>
    ';
}
?>

<!-- All Notifications Layer -->
<div id="allNotificationsLayer" class="fixed inset-0 z-50 flex items-center justify-center bg-opacity-50 hidden">
    <div class="<?php echo $currentColors['subbg']; ?> p-6 rounded-lg shadow-lg w-3/4 max-w-3xl max-h-[44rem] overflow-y-scroll">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold <?php echo $currentColors['text']; ?>">Toutes les Notifications</h2>
            <button onclick="toggleAllNotificationsLayer()" class="focus:outline-none <?php echo $currentColors['text']; ?>">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <ul id="allNotificationList" class="mt-4 space-y-2">
            <?php
            if(isset($_COOKIE['logged']) && $_COOKIE['logged'] != "NONE") {
                $allNotifications = $notificationsControleur->getAllNotifications();
                $allNotifications = array_reverse($allNotifications);
                foreach ($allNotifications as $notification) {
                    echo '<li class="p-2 rounded-md '; echo $currentColors['bg']; echo'">
                    <h3 class="font-semibold '; echo $currentColors['text']; echo '">' . $notification->getTitle() . '</h3>
                    <p class="text-sm '; echo $currentColors['text']; echo '">' . $notification->getContent() . '</p>
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

<div class="absolute top-0 right-0 p-4 flex items-center space-x-2">

    <!-- Bouton pour signaler une absence -->
    <?php
    if ($role != null && $role != 'ELEVE') {
        echo '<div class="relative">
            <button onclick="toggleAbsencePopup()" class="focus:outline-none '; echo $currentColors['text']; echo '">
                <svg class="h-6 w-6" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <path d="M21.9,37c0-2.7,0.9-5.8,2.3-8.2c1.7-3,3.6-4.2,5.1-6.4c2.5-3.7,3-9,1.4-13c-1.6-4.1-5.4-6.5-9.8-6.4 s-8,2.8-9.4,6.9c-1.6,4.5-0.9,9.9,2.7,13.3c1.5,1.4,2.9,3.6,2.1,5.7c-0.7,2-3.1,2.9-4.8,3.7c-3.9,1.7-8.6,4.1-9.4,8.7 C1.3,45.1,3.9,49,8,49h17c0.8,0,1.3-1,0.8-1.6C23.3,44.5,21.9,40.8,21.9,37z"></path>
                        <path d="M46.4,28.5c-4.7-4.7-12.3-4.7-17,0c-4.7,4.7-4.7,12.3,0,17c4.7,4.7,12.3,4.7,17,0 C51.1,40.8,51.1,33.2,46.4,28.5z M42.9,39.1c0.4,0.4,0.4,1.1-0.1,1.5l-1.4,1.4c-0.4,0.4-0.9,0.4-1.3-0.1L38,39.8l-2.2,2.2 c-0.4,0.4-0.9,0.4-1.3-0.1L33,40.5c-0.4-0.4-0.5-0.9-0.1-1.3l2.2-2.2L33,34.8c-0.4-0.4-0.5-0.9-0.1-1.3l1.4-1.4 c0.4-0.4,1.1-0.5,1.5-0.1l2.1,2.1l2.1-2.1c0.4-0.4,1.1-0.5,1.5-0.1l1.4,1.4c0.4,0.4,0.4,1.1-0.1,1.5l-2.1,2.1L42.9,39.1z"></path>
                    </g>
                </svg>
            </button>
        </div>';
    }

    if (isset($_COOKIE['logged']) && $_COOKIE['logged'] != "NONE") {
        echo '
    <div class="relative">
        <button onclick="toggleNotificationSidebar()" class="focus:outline-none '; echo $currentColors['text']; echo '">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </button>
    </div>';
    }
    ?>

    <!-- Bouton pour basculer le thème -->
    <form action="theme.php" method="POST" class="flex items-center">
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

<!-- Pop-up pour signaler une absence -->
<div id="absencePopup" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="<?php echo $currentColors['subbg']; ?> p-6 rounded-lg shadow-lg w-3/4 max-w-3xl <?php echo $currentColors['bg']; ?> <?php echo $currentColors['text']; ?>">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold <?php echo $currentColors['text']; ?>">Signaler une absence</h2>
            <button onclick="toggleAbsencePopup()" class="focus:outline-none <?php echo $currentColors['text']; ?>">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form action="Dashboard.php" method="POST">
            <div class="mb-4">
                <label for="start-date" class="block text-sm font-medium <?php echo $currentColors['text']; ?>">Date de début</label>
                <input type="date" id="start-date" name="start-date" required class="mt-1 block w-full rounded-md <?php echo $currentColors['border']; ?> shadow-sm focus:<?php echo $currentColors['ring']; ?> sm:text-sm">
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
                <label for="end-date" class="block text-sm font-medium <?php echo $currentColors['text']; ?>">Date de fin</label>
                <input type="date" id="end-date" name="end-date" required class="mt-1 block w-full rounded-md <?php echo $currentColors['border']; ?> shadow-sm focus:<?php echo $currentColors['ring']; ?> sm:text-sm">
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
                <label for="reason" class="block text-sm font-medium <?php echo $currentColors['text']; ?>">Motif</label>
                <textarea id="reason" name="reason" rows="4" required class="mt-1 block w-full rounded-md <?php echo $currentColors['border']; ?> shadow-sm focus:<?php echo $currentColors['ring']; ?> sm:text-sm"></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" id="absence" name="absence" class="rounded-md <?php echo $currentColors['bg']; ?> px-3 py-1.5 text-sm font-semibold leading-6 <?php echo $currentColors['text']; ?> shadow-sm <?php echo $currentColors['hover']; ?> focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:<?php echo $currentColors['ring']; ?>">Soumettre</button>
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
<div id="sidebar" class="lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-16 lg:flex-col transition-width duration-300 <?php echo $currentColors['bg']; ?>">
    <!-- Bouton pour rétracter/étendre la sidebar -->
    <div class="absolute top-0 right-0 p-2">
        <button onclick="toggleSidebar()" class="focus:outline-none <?php echo $currentColors['text']; ?>">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>
    </div>
    <!-- Sidebar component-->
    <div class="flex grow flex-col gap-y-5 overflow-y-auto px-6 pb-4 pt-10 <?php echo $currentColors['subbg']; ?>">
        <!-- Calendrier -->
        <div class="mt-10 text-center lg:col-start-8 lg:col-end-13 lg:row-start-1 lg:mt-9 xl:col-start-9 hide-when-collapsed hidden">
            <!-- Boutons mois précédents/suivants -->
            <div class="flex items-center <?php echo $currentColors['text']; ?>">
                <form action="Dashboard.php" method="POST" class="flex w-full">
                    <button type="submit" name="monthOffSet" value="<?php echo ($_SESSION['monthOffSet'] - 1); ?>" class="-m-1.5 flex flex-none items-center justify-center p-1.5 <?php echo $currentColors['text']; ?> hover:<?php echo $currentColors['hover']; ?>">
                        <span class="sr-only">Mois précédent</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div class="flex-auto text-sm font-semibold <?php echo $currentColors['text']; ?>"><?php echo ucfirst($month); ?></div>
                    <button type="submit" name="monthOffSet" value="<?php echo ($_SESSION['monthOffSet'] + 1); ?>" class="-m-1.5 flex flex-none items-center justify-center p-1.5 <?php echo $currentColors['text']; ?> hover:<?php echo $currentColors['hover']; ?>">
                        <span class="sr-only">Mois suivant</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Affichage calendrier -->
            <div class="mt-6 grid grid-cols-7 text-xs leading-6 <?php echo $currentColors['text']; ?>">
                <div>Lun</div><div>Mar</div><div>Mer</div><div>Jeu</div><div>Ven</div><div>Sam</div><div>Dim</div>
            </div>
            <div class="isolate mt-2 grid grid-cols-7 gap-px rounded-lg <?php echo $currentColors['bg']; ?> text-sm <?php echo $currentColors['shadow']; ?> ring-1 ring-black">
                <?php $controleur->generateCalendar(); ?>
            </div>
        </div>

        <!-- Sidebar footer -->
        <div class="mt-auto flex-col justify-center hide-when-collapsed hidden">
            <?php
            if ($role != null && $role == "GESTIONNAIRE") {

                echo '<form class="mb-4 flex justify-center" action="Dashboard.php" method="POST">
                        <div>
                            <label for="newGroupe" class="block text-sm font-medium leading-6 '; echo $currentColors['text']; echo '">Groupe : ' . $_COOKIE['formation'] . '-' . $_COOKIE['groupe'] . $_COOKIE['sousgroupe'] . '  </label>
                            <div class="mt-2">
                                <select id="newGroupe" name="newGroupe" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset '; echo $currentColors['ring']; echo ' focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                </select>
                            </div>
                        </div>
                        <div>
                            <button type="submit" id="change-groupe" name="change-groupe" class="rounded '; echo $currentColors['bg']; echo ' px-2 py-1 text-xs font-semibold '; echo $currentColors['text']; echo ' shadow-sm ring-1 ring-inset '; echo $currentColors['ring']; echo ' hover:';  echo $currentColors['hover']; echo '">Changer de groupe</button>
                        </div>
                    </form>';

                echo '<form action="Dashboard.php" method="POST" class="mb-4 flex justify-center">
                <button type="submit" id="gestio-ping-modification" name="gestio-ping-modification" class="rounded bg-gray-800 px-2 py-1 text-xs font-semibold text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-900">Notifier changement EDT</button>
            </form>';
            }
            ?>

            <form action="Dashboard.php" method="POST" class="mb-4 flex justify-center">
                <button type="submit" id="disconnect" name="disconnect" class="rounded <?php echo $currentColors['bg']; ?> px-2 py-1 text-xs font-semibold <?php echo $currentColors['text']; ?> shadow-sm ring-1 ring-inset '; echo $currentColors['ring']; echo ' hover:<?php echo $currentColors['hover']; ?>">Se déconnecter</button>
            </form>
            <span class="text-xs <?php echo $currentColors['text']; ?>">Version: <?php echo $version; if(isset($_COOKIE['role']) && $_COOKIE['role'] != "NONE" && $_COOKIE['role'] != 'ELEVE') { echo ' - Vous êtes ' . $_COOKIE['role']; } ?></span>
            <svg xmlns="http://www.w3.org/2000/svg" width="240" height="1" viewBox="0 0 240 1" fill="none">
                <path d="M0 0.5H240" stroke="#898888"/>
            </svg>
            <form action="Dashboard.php" method="POST">
                <div class="mt-4 flex justify-center gap-12">
                    <button type="submit" name="ICAL" value="ICAL" style="border: none; background: none; padding: 0;">
                        <svg height="30" width="30" viewBox="0 0 512 512" fill="currentColor">
                            <g>
                                <path d="M118.612,89.298c9.482,0,17.176-7.686,17.176-17.168v-54.96C135.788,7.687,128.094,0,118.612,0 c-9.482,0-17.177,7.687-17.177,17.169v54.96C101.435,81.611,109.13,89.298,118.612,89.298z"></path>
                                <path d="M255.992,89.298c9.482,0,17.177-7.686,17.177-17.168v-54.96C273.169,7.687,265.474,0,255.992,0 c-9.483,0-17.176,7.687-17.176,17.169v54.96C238.816,81.611,246.51,89.298,255.992,89.298z"></path>
                                <path d="M393.372,89.298c9.483,0,17.176-7.686,17.176-17.168v-54.96C410.549,7.687,402.855,0,393.372,0 c-9.483,0-17.176,7.687-17.176,17.169v54.96C376.196,81.611,383.89,89.298,393.372,89.298z"></path>
                                <path d="M186.593,405.072c32.102,0,56.031-21.572,56.031-54.189c0-23.421-13.152-38.94-23.667-43.411v-0.524 c13.152-8.68,19.982-22.102,19.982-37.097c0-28.942-19.196-49.456-52.608-49.456c-28.942,0-48.678,16.575-52.624,38.146 c-0.262,1.573,0.524,2.892,2.112,3.154l22.357,5.264c1.588,0.263,2.637-0.262,3.161-1.842 c3.161-11.048,10.793-18.418,23.945-18.418c14.988,0,23.93,9.999,23.93,24.207c0,15.782-10.253,26.043-26.042,26.043h-7.108 c-1.572,0-2.622,1.048-2.622,2.629v20.784c0,1.58,1.049,2.629,2.622,2.629h7.632c17.623,0,29.203,11.055,29.203,29.203 c0,15.264-9.729,26.574-26.304,26.574c-14.74,0-23.421-8.419-27.106-18.942c-0.524-1.58-1.836-2.104-3.146-1.842l-22.897,5.266 c-1.572,0.524-2.359,1.834-2.097,3.415C136.868,391.133,158.701,405.072,186.593,405.072z"></path>
                                <path d="M296.836,277.744l27.352-19.204h0.524v141.011c0,1.58,1.064,2.63,2.637,2.63h24.207 c1.573,0,2.622-1.049,2.622-2.63V225.914c0-1.573-1.048-2.629-2.622-2.629h-22.372c-2.097,0-3.408,0.524-4.734,1.58l-28.663,20.784 c-1.326,1.048-1.85,2.105-1.85,3.678v26.836C293.938,278.269,295.248,278.793,296.836,277.744z"></path>
                                <path d="M427.001,44.899h-2.714v27.23c0,17.038-13.861,30.906-30.914,30.906c-17.038,0-30.914-13.869-30.914-30.906 v-27.23h-75.551v27.23c0,17.038-13.877,30.906-30.914,30.906s-30.914-13.869-30.914-30.906v-27.23h-75.552v27.23 c0,17.038-13.876,30.906-30.914,30.906S87.698,89.167,87.698,72.129v-27.23h-2.698c-37.082,0-67.133,30.059-67.133,67.133v332.835 c0,37.074,30.051,67.133,67.133,67.133h342.002c37.066,0,67.133-30.058,67.133-67.133V112.032 C494.134,74.958,464.067,44.899,427.001,44.899z M450.854,439.772c0,15.973-12.99,28.964-28.956,28.964H90.103 c-15.973,0-28.971-12.99-28.971-28.964V143.116h389.722V439.772z"></path>
                            </g>
                        </svg>
                    </button>

                    <button type="submit" name="PDF" value="PDF" style="border: none; background: none; padding: 0;">
                        <svg height="30" width="30" viewBox="0 0 512 512" fill="currentColor">
                            <g>
                                <path d="M378.413,0H208.297h-13.182L185.8,9.314L57.02,138.102l-9.314,9.314v13.176v265.514c0,47.36,38.528,85.895,85.896,85.895h244.811c47.353,0,85.881-38.535,85.881-85.895V85.896C464.294,38.528,425.766,0,378.413,0z M432.497,426.105c0,29.877-24.214,54.091-54.084,54.091H133.602c-29.884,0-54.098-24.214-54.098-54.091V160.591h83.716c24.885,0,45.077-20.178,45.077-45.07V31.804h170.116c29.87,0,54.084,24.214,54.084,54.092V426.105z"></path>
                                <path d="M171.947,252.785h-28.529c-5.432,0-8.686,3.533-8.686,8.825v73.754c0,6.388,4.204,10.599,10.041,10.599c5.711,0,9.914-4.21,9.914-10.599v-22.406c0-0.545,0.279-0.817,0.824-0.817h16.436c20.095,0,32.188-12.226,32.188-29.612C204.136,264.871,192.182,252.785,171.947,252.785z M170.719,294.888h-15.208c-0.545,0-0.824-0.272-0.824-0.81v-23.23c0-0.545,0.279-0.816,0.824-0.816h15.208c8.42,0,13.447,5.027,13.447,12.498C184.167,290,179.139,294.888,170.719,294.888z"></path>
                                <path d="M250.191,252.785h-21.868c-5.432,0-8.686,3.533-8.686,8.825v74.843c0,5.3,3.253,8.693,8.686,8.693h21.868c19.69,0,31.923-6.249,36.81-21.324c1.76-5.3,2.723-11.681,2.723-24.857c0-13.175-0.964-19.557-2.723-24.856C282.113,259.034,269.881,252.785,250.191,252.785z M267.856,316.896c-2.318,7.331-8.965,10.459-18.21,10.459h-9.23c-0.545,0-0.824-0.272-0.824-0.816v-55.146c0-0.545,0.279-0.817,0.824-0.817h9.23c9.245,0,15.892,3.128,18.21,10.46c0.95,3.128,1.62,8.56,1.62,17.93C269.476,308.336,268.805,313.768,267.856,316.896z"></path>
                                <path d="M361.167,252.785h-44.812c-5.432,0-8.7,3.533-8.7,8.825v73.754c0,6.388,4.218,10.599,10.055,10.599c5.697,0,9.914-4.21,9.914-10.599v-26.351c0-0.538,0.265-0.81,0.81-0.81h26.086c5.837,0,9.23-3.532,9.23-8.56c0-5.028-3.393-8.553-9.23-8.553h-26.086c-0.545,0-0.81-0.272-0.81-0.817v-19.425c0-0.545,0.265-0.816,0.81-0.816h32.733c5.572,0,9.245-3.666,9.245-8.553C370.411,256.45,366.738,252.785,361.167,252.785z"></path>
                            </g>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--Dashboard-->
<div id="dashboard" class="lg:pl-16 transition-all duration-300 <?php echo $currentColors['bg']; ?>">
    <div class="flex h-full flex-col">
        <!-- topbar (changeur de semaines) -->
        <header class="flex justify-between items-center border-b <?php echo $currentColors['border']; ?> px-4 py-2">
            <form action="DayView.php" method="GET" class="flex items-center">
                <input type="hidden" name="dayOffSet" value="<?php echo $_SESSION['dayOffSet']; ?>">
                <button type="submit" class="flex items-center <?php echo $currentColors['text']; ?> <?php echo $currentColors['hover']; ?>">
                    <svg viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="h-6 w-6">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <title>exit_full_screen [#905]</title>
                            <desc>Created with Sketch.</desc>
                            <defs></defs>
                            <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <g id="Dribbble-Light-Preview" transform="translate(-260.000000, -4199.000000)" fill="#000000">
                                    <g id="icons" transform="translate(56.000000, 160.000000)">
                                        <path d="M218,4047 L224,4047 L224,4045 L218,4045 L218,4039 L216,4039 L216,4043.959 L216,4047 L218,4047 Z M218,4053 L224,4053 L224,4051 L218,4051 L216,4051 L216,4051.959 L216,4059 L218,4059 L218,4053 Z M210,4059 L212,4059 L212,4051.959 L212,4051 L210,4051 L204,4051 L204,4053 L210,4053 L210,4059 Z M210,4039 L212,4039 L212,4043.959 L212,4047 L210,4047 L204,4047 L204,4045 L210,4045 L210,4039 Z" id="exit_full_screen-[#905]"></path>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </svg>
                </button>
            </form>
            <form action="Dashboard.php" method="POST" class="flex items-center mx-auto">
                <input type="hidden" name="weekOffSet" value="<?php echo $_SESSION['weekOffSet']; ?>">
                <div class="flex items-center rounded-md <?php echo $currentColors['bg'] ?> shadow-sm md:items-stretch">
                    <button type="submit" name="weekOffSet" value="<?php echo ($_SESSION['weekOffSet'] - 1); ?>" class="flex h-9 w-12 items-center justify-center rounded-l-md border-y border-l <?php echo $currentColors['border']; ?> pr-1 <?php echo $currentColors['text']; ?> <?php echo $currentColors['hover']; ?> focus:relative md:w-9 md:pr-0 md:hover:<?php echo $currentColors['bg']; ?>">
                        <span class="sr-only">Semaine précédente</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button type="submit" name="weekOffSet" value="0" class="hidden border-y <?php echo $currentColors['border']; ?> px-3.5 text-sm font-semibold <?php echo $currentColors['text']; ?> <?php echo $currentColors['hover']; ?> focus:relative md:block">
                        Du <?php $fDay = getWeekDay(true); echo $fDay->format('d M') ?> au <?php $lDay = getWeekDay(false); echo $lDay->format('d M'); ?>
                    </button>
                    <span class="relative -mx-px h-5 w-px <?php echo $currentColors['bg']; ?> md:hidden"></span>
                    <button type="submit" name="weekOffSet" value="<?php echo ($_SESSION['weekOffSet'] + 1); ?>" class="flex h-9 w-12 items-center justify-center rounded-r-md border-y border-r <?php echo $currentColors['border']; ?> pl-1 <?php echo $currentColors['text']; ?> <?php echo $currentColors['hover']; ?> focus:relative md:w-9 md:pl-0 md:hover:<?php echo $currentColors['bg']; ?>">
                        <span class="sr-only">Semaine suivante</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </form>
        </header>

        <!-- Content -->
        <div class="isolate flex flex-auto flex-col overflow-hidden <?php echo $currentColors['bg']; ?>">
            <div class="flex max-w-full flex-none flex-col sm:max-w-none md:max-w-full">
                <div class="sticky top-0 z-30 flex-none <?php echo $currentColors['bg']; ?> <?php echo $currentColors['shadow']; ?> ring-1 ring-black ring-opacity-5 sm:pr-8">
                    <div class="-mr-px hidden grid-cols-5 divide-x <?php echo $currentColors['lines'] ?> border-r <?php echo $currentColors['border']; ?> text-sm leading-6 <?php echo $currentColors['text']; ?> sm:grid">
                        <div class="col-end-1 w-14"></div>
                        <!-- affichage de la semaine -->
                        <div class="flex items-center justify-center py-3 <?php echo $currentColors['hover']; ?>" data-date="<?php $thisDay = getDayWeek('monday'); echo $thisDay->format('Y-m-d') ?> " onclick="handleDayClicked(this)">
                            <span>Lun <span class="items-center justify-center font-semibold <?php echo $currentColors['text']; ?>"><?php $thisDay = getDayWeek('monday'); echo $thisDay->format('d M'); ?></span></span>
                        </div>
                        <div class="flex items-center justify-center py-3 <?php echo $currentColors['hover']; ?>" data-date="<?php $thisDay = getDayWeek('tuesday'); echo $thisDay->format('Y-m-d') ?> " onclick="handleDayClicked(this)">
                            <span>Mar <span class="items-center justify-center font-semibold <?php echo $currentColors['text']; ?>"><?php $thisDay = getDayWeek('tuesday'); echo $thisDay->format('d M'); ?></span></span>
                        </div>
                        <div class="flex items-center justify-center py-3 <?php echo $currentColors['hover']; ?>" data-date="<?php $thisDay = getDayWeek('wednesday'); echo $thisDay->format('Y-m-d') ?> " onclick="handleDayClicked(this)">
                            <span>Mer <span class="items-center justify-center font-semibold <?php echo $currentColors['text']; ?>"><?php $thisDay = getDayWeek('wednesday'); echo $thisDay->format('d M'); ?></span></span>
                        </div>
                        <div class="flex items-center justify-center py-3 <?php echo $currentColors['hover']; ?>" data-date="<?php $thisDay = getDayWeek('thursday'); echo $thisDay->format('Y-m-d') ?> " onclick="handleDayClicked(this)">
                            <span>Jeu <span class="items-center justify-center font-semibold <?php echo $currentColors['text']; ?>"><?php $thisDay = getDayWeek('thursday'); echo $thisDay->format('d M'); ?></span></span>
                        </div>
                        <div class="flex items-center justify-center py-3 <?php echo $currentColors['hover']; ?>" data-date="<?php $thisDay = getDayWeek('friday'); echo $thisDay->format('Y-m-d') ?> " onclick="handleDayClicked(this)">
                            <span>Ven <span class="items-center justify-center font-semibold <?php echo $currentColors['text']; ?>"><?php $thisDay = getDayWeek('friday'); echo $thisDay->format('d M'); ?></span></span>
                        </div>
                    </div>
                </div>

                <!-- Heures sur la gauche du calendrier -->
                <div class="flex flex-auto">
                    <div class="sticky left-0 z-10 w-14 flex-none <?php echo $currentColors['bg']; ?> ring-1 <?php echo $currentColors['ring'] ?>"></div>
                    <div class="grid flex-auto grid-cols-1 grid-rows-1">
                        <!-- Vertical lines -->
                        <div class="col-start-1 col-end-2 row-start-1 grid-cols-5 grid-rows-1 divide-x <?php echo $currentColors['lines'] ?> sm:grid sm:grid-cols-5">
                            <div class="col-start-1 row-span-full"></div>
                            <div class="col-start-2 row-span-full"></div>
                            <div class="col-start-3 row-span-full"></div>
                            <div class="col-start-4 row-span-full"></div>
                            <div class="col-start-5 row-span-full"></div>
                            <div class="col-start-6 row-span-full w-8"></div>
                        </div>

                        <!-- Horizontal lines -->
                        <div class="col-start-1 col-end-2 row-start-1 grid divide-y <?php echo $currentColors['lines'] ?>" style="grid-template-rows: repeat(19, minmax(4.2vh , 1fr))">
                            <!-- Adjust top margin for mobile -->
                            <div class="row-end-1 h-7"></div>
                            <?php
                            for ($i = 8; $i <= 17; $i++) {
                                echo '<div>
                                    <div class="sticky left-0 z-20 -ml-14 -mt-2.5 w-14 pr-2 text-right text-xs leading-5 '; echo $currentColors['text']; echo '">' . $i . '</div>
                                    </div>
                                    <div></div>';
                            }
                            ?>
                        </div>

                        <!-- Events -->
                        <ol class="col-start-1 col-end-2 row-start-1 grid grid-cols-1 sm:grid-cols-5 sm:pr-8" style="grid-template-rows: 1.75rem repeat(19, minmax(4.2vh, 1fr)) auto">
                            <?php $controleur->generateDays($week, false, (isset($_COOKIE['collegue']) && $_COOKIE['collegue'] != "NONE")); ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
