<?php
include_once("../controleur/Controleur.php");
include_once("../controleur/NotificationControleur.php");
include_once("../controleur/UtilsControleur.php");

session_start();

$controleur = new Controleur();
$notificationsControleur = new NotificationControleur();

// Vérification si l'utilisateur n'est pas connecté
if(!isset($_COOKIE['groupe']) || $_COOKIE['groupe'] == "NONE") {
    header('location: Login.php');
    exit();
}

// Données de bases
setlocale(LC_TIME, 'fr_FR.UTF-8');
$realDate = new DateTime('now', new DateTimeZone('Europe/Paris'));
$date = clone $realDate;
$week = clone $realDate;

// Vérification si l'utilisateur à demander de changer de semaine
if (isset($_POST['weekOffSet'])) {
    $_SESSION['weekOffSet'] = (int)$_POST['weekOffSet'];
} else {
    // Si ce n'est pas le cas, si aucune semaine n'est enregistrer dans la session, alors on définit à la semaine actuelle.
    if (!isset($_SESSION['weekOffSet'])) {
        $_SESSION['weekOffSet'] = 0;
    }
}

if (isset($_GET['error']) && $_GET['error'] == 'validation_expired') {
    echo "<p style='color: red;'>Le délai de 24 heures pour valider ou refuser est dépassé.</p>";
}

// Modification des données par rapport à l'utilisateur
$week = $week->modify(($_SESSION['weekOffSet'] * 7) . ' days');
$month = IntlDateFormatter::formatObject($date, 'MMMM y', 'fr');

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

    // Calculate the grid row based on the hour and minute
    $gridRow = (($hour - 7) * 2) + 1; // Adjusted to start from 1
    if ($minute >= 30) {
        $gridRow += 1;
    }

    // Ensure the grid row is within the valid range
    if ($gridRow < 1) {
        $gridRow = 1;
    } elseif ($gridRow > 19) {
        $gridRow = 19;
    }

    // Debugging output
    error_log("DateTime: " . $dateTime->format('Y-m-d H:i') . " -> GridRow: " . $gridRow);

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

function getDayWeek($day) {
    global $week;
    $resultDate = clone $week;
    $resultDate->modify($day . ' this week');

    return $resultDate;
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

function isCourseModified($currentCourse, $previousCourses) {
    foreach ($previousCourses as $prevCourse) {
        if ((string)$currentCourse == (string)$prevCourse) {
            return false;
        }
    }
}

include_once "../controleur/AbsenceControleur.php";
$absenceControleur = new AbsenceControleur();
$absences = $absenceControleur->getAllAbsences();
$filteredAbsences = array_filter($absences, function($absence) {
    $dayOfWeek = date('N', strtotime($absence->getStart()));
    return $dayOfWeek >= 1 && $dayOfWeek <= 5;
});

function generateDays2($week, $isPreviousVersion = false) {
    $weekDates = getWeekDates($week);
    $disciplineColors = getDisciplineColors();

    foreach ($weekDates as $weekDate) {
        if ($isPreviousVersion) {
            $courses = getDayPreviousVersion($weekDate, $weekDate->format('d'), $_COOKIE['semestre'], $_COOKIE['groupe'], (int) $_COOKIE['sousgroupe'], $_COOKIE['formation']);
        } else {
            $courses = getDay($weekDate, $weekDate->format('d'), $_COOKIE['semestre'], $_COOKIE['groupe'], (int) $_COOKIE['sousgroupe'], $_COOKIE['formation']);
            $previousCourses = getDayPreviousVersion($weekDate, $weekDate->format('d'), $_COOKIE['semestre'], $_COOKIE['groupe'], (int) $_COOKIE['sousgroupe'], $_COOKIE['formation']);
        }

        $alreadyPlace = [];
        foreach ($courses as $course) {
            if(in_array($course, $alreadyPlace))
                continue;

            $alreadyPlace[] = $course;

            $modificationReason = !$isPreviousVersion ? isCourseModified($course, $previousCourses) : false;
            $isModified = $modificationReason !== false;

            $horraire = new DateTime($course->getHoraire(), new DateTimeZone('Europe/Paris'));
            $dispHoraire = $horraire->format("N");
            $dispGridRow = getGridRow($horraire);
            $duree = new DateTime($course->getDuration(), new DateTimeZone('Europe/Paris'));
            $dispSpan = getSpan($duree);
            $color = array_key_exists($course->getDiscipline(), $disciplineColors) ? $disciplineColors[$course->getDiscipline()] : "gray";
            $dispHour = str_pad($horraire->format("H"), 2, '0', STR_PAD_LEFT);
            $dispMinute = str_pad($horraire->format("i"), 2, '0', STR_PAD_LEFT);
            $uniqueId = uniqid();

            echo '<li class="relative mt-px flex sm:col-start-' . $dispHoraire . '" style="grid-row: ' . $dispGridRow . ' / span ' . $dispSpan . '">
    <a class="group absolute inset-1 flex flex-col overflow-visible rounded-lg bg-' . $color . '-50 p-2 text-sm leading-5 hover:bg-' . $color . '-100 ' . ($isModified ? 'border-2 border-red-500' : '') . '">
        <form>
            <div>
                <p class="text-' . $color . '-500 font-semibold group-hover:text-' . $color . '-700">
                    <time>' . $dispHour . ':' . $dispMinute . '</time>
                </p>
                <p class="order-1 text-' . $color . '-700">' . $course->getTypeseance() . ' - ' . $course->getEnseignementShortName() . '</p>
            </div>
        </form>
        <!-- Bouton pour afficher l\'info-bulle  -->
        <button data-tooltip-target="tooltip-' . $uniqueId . '"
                class="select-none rounded-lg bg-transparent py-1 px-2 text-xs font-bold uppercase text-gray-500 hover:text-gray-700 focus:outline-none"
                style="position: absolute; top: 0; right: 0;">
            ⓘ
        </button>

        <!-- Info-bulle avec animation -->
        <div id="tooltip-' . $uniqueId . '"
             data-tooltip="tooltip-' . $uniqueId . '"
             class="absolute z-50 whitespace-normal break-words rounded-lg bg-gray-50 py-1.5 px-3 font-sans text-sm font-normal text-black focus:outline-none transition-opacity opacity-0 border border-black"
             style="width: 200px; right: -210px; top: 0; display: none;">
            <p class="text-center font-bold text-lg">' . $course->getTypeseance() . '</p>
            <span>Cours : </span><span class="text-purple-500">' . $course->getEnseignementLongName() . '</span><br>
            <span>Horaire : </span><span class="text-blue-500">' . $dispHour . ':' . $dispMinute . '</span><br>
            <span>Salle : </span><span class="text-green-500">' . ($course->getSalle() == '' ? 'Pas de salle' : ($course->getSalle() == '200' ? 'Amphi.' : 'Salle ' . $course->getSalle())) . '</span><br>
            <span>Groupe : </span><span class="text-red-500">' . $course->getNomgroupe() . '</span><br>';

            echo '</div>
    </a>
</li>';
        }
    }
}

$version = returnVersion();

$role = 'ELEVE';
if(isset($_COOKIE['role']) && $_COOKIE['role'] != "NONE")
    $role = $_COOKIE['role'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Comparaison des emplois du temps</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script
            type="module"
            src="https://unpkg.com/@material-tailwind/html@latest/scripts/tooltip.js">
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipButtons = document.querySelectorAll('[data-tooltip-target]');

            tooltipButtons.forEach(button => {
                const tooltipId = button.getAttribute('data-tooltip-target');
                const tooltip = document.getElementById(tooltipId);

                const showTooltip = () => {
                    tooltip.style.display = 'block';
                    tooltip.style.opacity = '1';
                    tooltip.style.padding = '5px';

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
                };

                const hideTooltip = () => {
                    tooltip.style.display = 'none';
                    tooltip.style.opacity = '0';
                    tooltip.style.padding = '0';
                };

                button.addEventListener('mouseenter', showTooltip);
                button.addEventListener('mouseleave', () => {
                    setTimeout(hideTooltip, 50); // Delay hiding the tooltip
                });
                button.addEventListener('click', () => {
                    if (tooltip.style.display === 'block') {
                        hideTooltip();
                    } else {
                        showTooltip();
                    }
                });

                // Touch events for mobile
                button.addEventListener('touchstart', showTooltip);
                button.addEventListener('touchend', () => {
                    setTimeout(hideTooltip, 5000); // Delay hiding the tooltip
                });
            });
        });
    </script>
    <style>
        body {
            overflow-x: hidden;
        }
    </style>
</head>
<body>
<!-- topbar (changeur de semaines) -->
<header class="flex justify-between items-center border-b border-gray-200 px-4 py-2">
    <!-- Back to Dashboard Button -->
    <a href="Dashboard.php" class="text-gray-500 hover:text-gray-700">
    <span class="isolate inline-flex rounded-md shadow-sm">
        <button type="button" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            <span>Retour au Dashboard</span>
        </button>
    </span>
    </a>
    <h2 class="text-sm font-semibold">Ancienne version</h2>
    <form action="Comparison.php" method="POST">
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
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5-4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </form>
    <h2 class="text-sm font-semibold">Nouvelle version</h2>
    <!-- Valider and Refuser Buttons -->
    <div class="flex space-x-2">
        <form method="POST" action="../modele/Validation.php" onsubmit="return showValidationModal(event)">
            <input type="hidden" name="justification" value="ok">
            <span class="isolate inline-flex rounded-md shadow-sm">
            <button type="button" class="relative inline-flex items-center rounded-l-md bg-green-50 px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-green-100 focus:z-10" onclick="showValidationModal(event)">Valider</button>
        </span>
        </form>
        <input type="hidden" name="action" value="REFUSE">
        <span class="isolate inline-flex rounded-md shadow-sm">
        <button type="submit" class="relative -ml-px inline-flex items-center rounded-r-md bg-red-50 px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-red-100 focus:z-10" onclick="document.getElementById('refuseModal').classList.remove('hidden')">Refuser</button>
    </span>
    </div>

    <!-- Validation Modal -->
    <div id="validationModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden z-50">
        <div class="bg-white p-8 rounded shadow-lg w-1/3">
            <h2 class="text-lg font-semibold mb-4">Confirmation de validation</h2>
            <p>Êtes-vous sûr de vouloir valider ?</p>
            <div class="flex justify-end space-x-2 mt-4">
                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600" onclick="document.getElementById('validationModal').classList.add('hidden')">Annuler</button>
                <button type="button" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600" onclick="submitValidationForm()">Valider</button>
            </div>
        </div>
    </div>

    <script>
        function showValidationModal(event) {
            event.preventDefault();
            document.getElementById('validationModal').classList.remove('hidden');
        }

        function submitValidationForm() {
            document.querySelector('form[onsubmit="return showValidationModal(event)"]').submit();
        }
    </script>
</header>

<!-- Refus -->
<div id="refuseModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden z-50">
    <div class="bg-white p-8 rounded shadow-lg w-1/3">
        <h2 class="text-lg font-semibold mb-4">Justification du refus</h2>
        <form action="../modele/Validation.php" method="post">
            <textarea name="justification" rows="6" class="w-full p-2 border border-gray-300 rounded mb-4" placeholder="Entrez la justification ici..."></textarea>
            <div class="flex justify-end space-x-2">
                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600" onclick="document.getElementById('refuseModal').classList.add('hidden')">Annuler</button>
                <button type="submit" name="action" value="REFUSE" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Refuser</button>
            </div>
        </form>
    </div>
</div>

<!-- Content -->
<div class="flex h-full flex-col">
    <div class="flex flex-auto">
        <!-- Emploi du temps 1 -->
        <div class="w-1/2 p-4 border-r-2 border-gray-200">
            <div class="flex max-w-full flex-none flex-col sm:max-w-none md:max-w-full bg-red-100">
                <div class="sticky top-0 z-30 flex-none bg-white shadow ring-1 ring-black ring-opacity-5 sm:pr-8">
                    <div class="-mr-px hidden grid-cols-5 divide-x divide-gray-100 border-r border-gray-100 text-sm leading-6 text-gray-500 sm:grid">
                        <div class="col-end-1 w-14"></div>
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
                    </div>
                </div>
                <div class="flex flex-auto">
                    <div class="sticky left-0 z-10 w-14 flex-none bg-white ring-1 ring-gray-100"></div>
                    <div class="grid flex-auto grid-cols-1 grid-rows-1">
                        <div class="col-start-1 col-end-2 row-start-1 grid-cols-5 grid-rows-1 divide-x divide-gray-200 sm:grid sm:grid-cols-5">
                            <div class="col-start-1 row-span-full"></div>
                            <div class="col-start-2 row-span-full"></div>
                            <div class="col-start-3 row-span-full"></div>
                            <div class="col-start-4 row-span-full"></div>
                            <div class="col-start-5 row-span-full"></div>
                            <div class="col-start-6 row-span-full w-8"></div>
                        </div>
                        <div class="col-start-1 col-end-2 row-start-1 grid divide-y divide-gray-200" style="grid-template-rows: repeat(19, minmax(4.2vh , 1fr))">
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
                        <ol class="col-start-1 col-end-2 row-start-1 grid grid-cols-1 sm:grid-cols-5 sm:pr-8" style="grid-template-rows: 1.75rem repeat(19, minmax(4.2vh, 1fr)) auto">
                            <?php generateDays2($week, true); // Previous version ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emploi du temps 2 -->
        <div class="w-1/2 p-4">
            <div class="flex max-w-full flex-none flex-col sm:max-w-none md:max-w-full bg-green-50">
                <div class="sticky top-0 z-30 flex-none bg-white shadow ring-1 ring-black ring-opacity-5 sm:pr-8">
                    <div class="-mr-px hidden grid-cols-5 divide-x divide-gray-100 border-r border-gray-100 text-sm leading-6 text-gray-500 sm:grid">
                        <div class="col-end-1 w-14"></div>
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
                    </div>
                </div>
                <div class="flex flex-auto">
                    <div class="sticky left-0 z-10 w-14 flex-none bg-white ring-1 ring-gray-100"></div>
                    <div class="grid flex-auto grid-cols-1 grid-rows-1">
                        <div class="col-start-1 col-end-2 row-start-1 grid-cols-5 grid-rows-1 divide-x divide-gray-200 sm:grid sm:grid-cols-5">
                            <div class="col-start-1 row-span-full"></div>
                            <div class="col-start-2 row-span-full"></div>
                            <div class="col-start-3 row-span-full"></div>
                            <div class="col-start-4 row-span-full"></div>
                            <div class="col-start-5 row-span-full"></div>
                            <div class="col-start-6 row-span-full w-8"></div>
                        </div>
                        <div class="col-start-1 col-end-2 row-start-1 grid divide-y divide-gray-200" style="grid-template-rows: repeat(19, minmax(4.2vh , 1fr))">
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
                        <ol class="col-start-1 col-end-2 row-start-1 grid grid-cols-1 sm:grid-cols-5 sm:pr-8" style="grid-template-rows: 1.75rem repeat(19, minmax(4.2vh, 1fr)) auto">
                            <?php generateDays2($week, false); // Current version ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
