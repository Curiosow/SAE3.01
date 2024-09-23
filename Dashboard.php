<?php
include 'managers/ScheduleManager.php';
include 'managers/CollegueManager.php';
include 'managers/EnseignementManager.php';

session_start();

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

function generateDays() {
    global $week;

    $weekDates = getWeekDates($week);
    foreach ($weekDates as $weekDate) {
        $courses = getDay($weekDate, $weekDate->format('d'), (int) getSemestre((int) $_SESSION['promotion'], $weekDate), $_SESSION['groupe'], (int) $_SESSION['sousgroupe'], $_SESSION['formation']);
        foreach ($courses as $course) {
            $horraire = new DateTime($course->getHoraire(), new DateTimeZone('Europe/Paris'));
            $dispHoraire = $horraire->format("N");
            $dispGridRow = getGridRow($horraire);

            $duree = new DateTime($course->getDuration(), new DateTimeZone('Europe/Paris'));
            $dispSpan = getSpan($duree);

            $color = 'red';
            switch ($course->getTypeseance()) {
                case 'CM':
                    $color = 'purple';
                    break;

                case "TD":
                    $color = 'blue';
                    break;

                case "TP":
                    $color = 'green';
                    break;

                case "DS":
                    $color = 'orange';
                    break;

                case "PRJ":
                    $color = 'yellow';
                    break;
            }

            $dispHour = (int)$horraire->format("H");
            //$dispHour = $dispHour + 1;
            if($dispHour < 10 && $dispHour > 1)
                $dispHour = '0' . $dispHour;

            $dispMinute = $horraire->format("i") . '';
            if($horraire->format("i") < 10 && $horraire->format("i") > 1)
                $dispMinute = '0' . $dispMinute;

            // type="submit" name="viewCourse" value="' . $ser = serialize($course); echo $ser . '"
            echo '<li class="relative mt-px flex sm:col-start-' . $dispHoraire . '" style="grid-row: ' . $dispGridRow . ' / span ' . $dispSpan . '">
                <a class="group absolute inset-1 flex flex-col overflow-y-auto rounded-lg bg-'. $color . '-50 p-2 text-xs leading-5 hover:bg-blue-100">
                <form>
                <button">
                    <p class="text-'. $color . '-500 group-hover:text-'. $color . '-700"><time>'. $dispHour . ':' . $dispMinute . ' - ' . ($course->getSalle() == '' ? 'Pas de salle' : ($course->getSalle() == '200' ? 'Amphi.' : 'Salle ' . $course->getSalle())) . '</time></p>
                    <p class="order-1 font-semibold text-'. $color . '-700">' . $course->getTypeseance() . ' - ' . removeAfterTiret($course->getCode()) . '</p>
                    <p class="order-1 font-semibold text-'. $color . '-700">' . getCollegueFullName($course->getCollegue()) . '</p>
                </button>
              </form>
              </a>';
        }
    }
}

function removeAfterTiret($string) {
    $parts = explode(' - ', $string);
    return $parts[0];
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

function getDayWeek($day) {
    global $week;
    $resultDate = clone $week;
    $resultDate->modify($day . ' this week');

    return $resultDate;
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
    <!-- Static sidebar -->
    <div class="lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
        <!-- Sidebar component-->
        <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-black px-6 pb-4">

            <!-- Calendrier -->
            <div class="mt-10 text-center lg:col-start-8 lg:col-end-13 lg:row-start-1 lg:mt-9 xl:col-start-9">

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
            <div class="mt-auto flex-col justify-center ">
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

    <div class="lg:pl-72">
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

                        <button type="submit" name="weekOffSet" value="0" class="hidden border-y border-gray-300 px-3.5 text-sm font-semibold text-gray-900 hover:bg-gray-50 focus:relative md:block">Du <?php $fDay = getWeekDay(true); echo $fDay->format('d') ?> au <?php $lDay = getWeekDay(false); echo $lDay->format('d') ?></button>

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
                        <div class="-mr-px hidden grid-cols-7 divide-x divide-gray-100 border-r border-gray-100 text-sm leading-6 text-gray-500 sm:grid">
                            <div class="col-end-1 w-14"></div>

                            <!-- affichage de la semaine -->
                            <div class="flex items-center justify-center py-3">
                                <span>Lun <span class="items-center justify-center font-semibold text-gray-900"><?php $thisDay = getDayWeek('monday'); echo $thisDay->format('d M'); ?></span></span>
                            </div>
                            <div class="flex items-center justify-center py-3">
                                <span>Mar <span class="items-center justify-center font-semibold text-gray-900"><?php $thisDay = getDayWeek('tuesday'); echo $thisDay->format('d M'); ?></span></span>
                            </div>
                            <div class="flex items-center justify-center py-3">
                                <span>Mer <span class="items-center justify-center font-semibold text-gray-900"><?php $thisDay = getDayWeek('wednesday'); echo $thisDay->format('d M'); ?></span></span>
                            </div>
                            <div class="flex items-center justify-center py-3">
                                <span>Jeu <span class="items-center justify-center font-semibold text-gray-900"><?php $thisDay = getDayWeek('thursday'); echo $thisDay->format('d M'); ?></span></span>
                            </div>
                            <div class="flex items-center justify-center py-3">
                                <span>Ven <span class="items-center justify-center font-semibold text-gray-900"><?php $thisDay = getDayWeek('friday'); echo $thisDay->format('d M'); ?></span></span>
                            </div>
                            <div class="flex items-center justify-center py-3">
                                <span>Sam <span class="items-center justify-center font-semibold text-gray-900"><?php $thisDay = getDayWeek('saturday'); echo $thisDay->format('d M'); ?></span></span>
                            </div>
                            <div class="flex items-center justify-center py-3">
                                <span>Dim <span class="items-center justify-center font-semibold text-gray-900"><?php $thisDay = getDayWeek('sunday'); echo $thisDay->format('d M'); ?></span></span>
                            </div>

                        </div>
                    </div>

                    <!-- Vertical lines -->
                    <div class="col-start-1 col-end-2 row-start-1 hidden grid-cols-7 grid-rows-1 divide-x divide-gray-100 sm:grid sm:grid-cols-7">
                        <div class="col-start-1 row-span-full"></div>
                        <div class="col-start-2 row-span-full"></div>
                        <div class="col-start-3 row-span-full"></div>
                        <div class="col-start-4 row-span-full"></div>
                        <div class="col-start-5 row-span-full"></div>
                        <div class="col-start-6 row-span-full"></div>
                        <div class="col-start-7 row-span-full"></div>
                        <div class="col-start-8 row-span-full w-8"></div>
                    </div>

                    <!-- Heures sur la gauche du calendrier -->
                    <div class="flex flex-auto">
                        <div class="sticky left-0 z-10 w-14 flex-none bg-white ring-1 ring-gray-100"></div>
                        <div class="grid flex-auto grid-cols-1 grid-rows-1">
                            <!-- Horizontal lines -->
                            <div class="col-start-1 col-end-2 row-start-1 grid divide-y divide-gray-100" style="grid-template-rows: repeat(21, minmax(4vh , 1fr))">
                                <!-- Adjust top margin for mobile -->
                                <div class="row-end-1 h-7"></div>
                                <?php
                                for ($i = 8; $i <= 18; $i++) {
                                    echo '<div>
                                    <div class="sticky left-0 z-20 -ml-14 -mt-2.5 w-14 pr-2 text-right text-xs leading-5 text-gray-400">' . $i . '</div>
                                    </div>
                                    <div></div>';
                                }
                                ?>
                            </div>

                            <!-- Vertical lines -->
                            <div class="col-start-1 col-end-2 row-start-1 hidden grid-cols-7 grid-rows-1 divide-x divide-gray-200 sm:grid sm:grid-cols-7">
                                <div class="col-start-1 row-span-full"></div>
                                <div class="col-start-2 row-span-full"></div>
                                <div class="col-start-3 row-span-full"></div>
                                <div class="col-start-4 row-span-full"></div>
                                <div class="col-start-5 row-span-full"></div>
                                <div class="col-start-6 row-span-full"></div>
                                <div class="col-start-7 row-span-full"></div>
                                <div class="col-start-8 row-span-full w-8"></div>
                            </div>

                            <!-- Events -->
                            <ol class="col-start-1 col-end-2 row-start-1 grid grid-cols-1 sm:grid-cols-7 sm:pr-9" style="grid-template-rows: 1.75rem repeat(21, minmax(4vh, 1fr)) auto">
                                <!-- grid-row correspond à l'heure de début du cours, en partant de 7h30 qui correspond à 1-->
                                <!-- Chaque incrément de cet argument augmente de 30 minutes le début, par exemple 8h : 2; 9h : 4 etc-->
                                <!-- col-start correspond au jour de la semaine-->
                                <!-- span correspond à la durée du cours, plus précisemment au nombre de demi heures, par exemple 1h30 : 3-->
                                <?php generateDays(); ?>
                            </ol>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>
</html>