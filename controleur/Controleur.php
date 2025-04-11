<?php
include_once "../modele/managers/CollegueManager.php";
include_once "../modele/managers/EnseignementManager.php";
include_once "../modele/managers/ScheduleManager.php";
include_once("../vue/theme.php");

class Controleur
{
    public function __construct() {

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

    function getGridRow(DateTime $dateTime) {
        $hour = (int) $dateTime->format('H');
        $minute = (int) $dateTime->format('i');

        $gridRow = (($hour - 7) * 2);
        if ($minute >= 30) {
            $gridRow += 1;
        }
        return $gridRow;
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

    function generateDays($week, $previousVersion = false, $teacherEdt = false) {
        global $currentColors;
        $weekDates = $this->getWeekDates($week);

        $disciplineColors = getDisciplineColors();

        foreach ($weekDates as $weekDate) {

            if (!$previousVersion) {
                $courses = getDay($weekDate, $weekDate->format('d'), $_COOKIE['semestre'], $_COOKIE['groupe'], (int) $_COOKIE['sousgroupe'], $_COOKIE['formation'], $teacherEdt);
            } else {
                $courses = getDayPreviousVersion($weekDate, $weekDate->format('d'), $_COOKIE['semestre'], $_COOKIE['groupe'], (int) $_COOKIE['sousgroupe'], $_COOKIE['formation'], $teacherEdt);
            }


            $alreadyPlace = [];
            foreach ($courses as $course) {
                if(in_array($course, $alreadyPlace))
                    continue;

                $alreadyPlace[] = $course;

                $horraire = new DateTime($course->getHoraire(), new DateTimeZone('Europe/Paris'));
                $dispHoraire = $horraire->format("N");
                $dispGridRow = $this->getGridRow($horraire);

                $duree = new DateTime($course->getDuration(), new DateTimeZone('Europe/Paris'));
                $dispSpan = max($this->getSpan($duree), ceil(strlen($course->getTypeseance()) / 20) + 1);



                $color="gray";
                if(array_key_exists($course->getDiscipline(), $disciplineColors))
                    $color = $disciplineColors[$course->getDiscipline()];

                $dispHour = (int)$horraire->format("H");
                //$dispHour = $dispHour + 1;
                if($dispHour < 10 && $dispHour > 1)
                    $dispHour = '0' . $dispHour;

                $dispMinute = $horraire->format("i") . '';
                if($horraire->format("i") < 10 && $horraire->format("i") > 1)
                    $dispMinute = '0' . $dispMinute;

                $uniqueId = uniqid(); // Génère un identifiant unique

                $exam = "";

                if($course->getExam() == 't') {
                    $exam = " - EXAMEN";
                }

                $caseColor = "bg-" . $color . '-50';
                if($_COOKIE['theme'] == 'dark')
                    $caseColor = "bg-" . $color . '-500';

                $hoverCaseColor = "bg-" . $color . '-100';
                if($_COOKIE['theme'] == 'dark')
                    $hoverCaseColor = "bg-" . $color . '-700';

                $firstTtextColor = "text-" . $color . '-500';
                if($_COOKIE['theme'] == 'dark')
                    $firstTtextColor = "text-" . $color . '-50';

                $secondTtextColor = "text-" . $color . '-700';
                if($_COOKIE['theme'] == 'dark')
                    $secondTtextColor = "text-" . $color . '-100';

                echo '<li class="relative mt-px flex col-start-' . $dispHoraire . ' sm:col-start-' . $dispHoraire . '" style="grid-row: ' . $dispGridRow . ' / span ' . $dispSpan . '">
    <a class="group absolute inset-1 flex flex-col overflow-visible rounded-lg ' . $caseColor . ' p-2 text-sm sm:text-xs md:text-xs lg:text-sm leading-5 hover:' . $hoverCaseColor . '">
        <form>
            <div>
                <p class="' . $firstTtextColor . ' font-semibold text-sm sm:text-xs md:text-xs lg:text-sm group-hover:' . $secondTtextColor . '">
                    <time>' . $dispHour . ':' . $dispMinute . ' - ' . ($course->getSalle() == '' ? 'Pas de salle' : ($course->getSalle() == '200' ? 'Amphi.' : 'Salle ' . $course->getSalle())) . $exam . '</time>
                </p>
                <p class="order-1 ' . $secondTtextColor . ' text-sm sm:text-xs md:text-xs lg:text-sm">' . $course->getTypeseance() . ' - ' . $course->getEnseignementShortName() . '</p>
                <p class="order-1 ' . $secondTtextColor . ' text-sm sm:text-xs md:text-xs lg:text-sm">' . $this->transformTeacherName($course->getCollegueFullName()) . '</p>
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
             class="absolute z-50 whitespace-normal break-words rounded-lg ' . $currentColors['bg'] . ' py-1.5 px-3 font-sans text-sm font-normal text-black focus:outline-none transition-opacity opacity-0 border border-black"
             style="width: 200px; right: -210px; top: 0; display: none;">
            <p class="text-center ' . $currentColors['text'] . ' font-bold text-lg ">' . $course->getTypeseance() . '</p>
            <span class="' . $currentColors['text'] . '">Cours : </span><span class="text-purple-500">' . $course->getEnseignementLongName() . '</span><br>
            <span class="' . $currentColors['text'] . '">Horaire : </span><span class="text-blue-500">' . $dispHour . ':' . $dispMinute . '</span><br>
            <span class="' . $currentColors['text'] . '">Salle : </span><span class="text-green-500">' . ($course->getSalle() == '' ? 'Pas de salle' : ($course->getSalle() == '200' ? 'Amphi.' : 'Salle ' . $course->getSalle())) . '</span><br>
            <span class="' . $currentColors['text'] . '">Groupe : </span><span class="text-red-500">' . $course->getNomgroupe() . '</span><br>';

                if($course->getCollegue() != '' && $course->getCollegue() != null) {
                    echo '<span class="' . $currentColors['text'] . '">Prof : </span> <span class="text-orange-500">' . $this->transformTeacherName($course->getCollegueFullName()) . '</span>';
                }

                echo '</div>
    </a>
</li>';
?>
<?php
            }
        }
    }

    function generateDay($day, $teacherEdt = false) {

        global $currentColors;
        $disciplineColors = getDisciplineColors();

        $courses = getDay($day, $day->format('d'), $_COOKIE['semestre'], $_COOKIE['groupe'], (int) $_COOKIE['sousgroupe'], $_COOKIE['formation'], $teacherEdt);

        $alreadyPlace = [];
        foreach ($courses as $course) {
            if (in_array($course, $alreadyPlace))
                continue;

            $alreadyPlace[] = $course;

            $horraire = new DateTime($course->getHoraire(), new DateTimeZone('Europe/Paris'));
            $dispHoraire = $horraire->format("N");
            $dispGridRow = $this->getGridRow($horraire);

            $duree = new DateTime($course->getDuration(), new DateTimeZone('Europe/Paris'));
            $dispSpan = $this->getSpan($duree);

            $color = "gray";
            if (array_key_exists($course->getDiscipline(), $disciplineColors))
                $color = $disciplineColors[$course->getDiscipline()];

            $dispHour = (int)$horraire->format("H");
            if ($dispHour < 10 && $dispHour > 1)
                $dispHour = '0' . $dispHour;

            $dispMinute = $horraire->format("i") . '';
            if ($horraire->format("i") < 10 && $horraire->format("i") > 1)
                $dispMinute = '0' . $dispMinute;

            $uniqueId = uniqid();

            $exam = "";
            if ($course->getExam() == 't') {
                $exam = " - EXAMEN";
            }

            $caseColor = "bg-" . $color . '-50';
            if($_COOKIE['theme'] == 'dark')
                $caseColor = "bg-" . $color . '-500';

            $hoverCaseColor = "bg-" . $color . '-100';
            if($_COOKIE['theme'] == 'dark')
                $hoverCaseColor = "bg-" . $color . '-700';

            $firstTtextColor = "text-" . $color . '-500';
            if($_COOKIE['theme'] == 'dark')
                $firstTtextColor = "text-" . $color . '-50';

            $secondTtextColor = "text-" . $color . '-700';
            if($_COOKIE['theme'] == 'dark')
                $secondTtextColor = "text-" . $color . '-100';

            echo '<li class="relative mt-px flex col-start-1 col-end-6" style="grid-row: ' . $dispGridRow . ' / span ' . $dispSpan . '">
                <a class="group absolute inset-1 flex flex-col overflow-visible rounded-lg ' . $caseColor . ' p-2 ' . ' hover:' . $hoverCaseColor . '">
                    <form>
                        <div>
                            <p class="' . $firstTtextColor . ' font-semibold group-hover:' . $secondTtextColor . '">
                                <time>' . $dispHour . ':' . $dispMinute . ' - ' . ($course->getSalle() == '' ? 'Pas de salle' : ($course->getSalle() == '200' ? 'Amphi.' : 'Salle ' . $course->getSalle())) . $exam . '</time>
                            </p>
                            <p class="order-1 ' . $secondTtextColor . '">' . $course->getTypeseance() . ' - ' . $course->getEnseignementShortName() . '</p>
                            <p class="order-1 ' . $secondTtextColor . '">' . $this->transformTeacherName($course->getCollegueFullName()) . '</p>
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
                         class="absolute z-50 whitespace-normal break-words rounded-lg ' . $currentColors['bg'] . ' py-1.5 px-3 font-sans text-sm font-normal text-black focus:outline-none transition-opacity opacity-0 border border-black"
                         style="width: 200px; right: -210px; top: 0; display: none;">
                        <p class="text-center ' . $currentColors['text'] . ' font-bold text-lg ">' . $course->getTypeseance() . '</p>
                        <span class="' . $currentColors['text'] . '">Cours : </span><span class="text-purple-500">' . $course->getEnseignementLongName() . '</span><br>
                        <span class="' . $currentColors['text'] . '">Horaire : </span><span class="text-blue-500">' . $dispHour . ':' . $dispMinute . '</span><br>
                        <span class="' . $currentColors['text'] . '">Salle : </span><span class="text-green-500">' . ($course->getSalle() == '' ? 'Pas de salle' : ($course->getSalle() == '200' ? 'Amphi.' : 'Salle ' . $course->getSalle())) . '</span><br>
                        <span class="' . $currentColors['text'] . '">Groupe : </span><span class="text-red-500">' . $course->getNomgroupe() . '</span><br>';

                        if($course->getCollegue() != '' && $course->getCollegue() != null) {
                            echo '<span class="' . $currentColors['text'] . '">Prof : </span> <span class="text-orange-500">' . $this->transformTeacherName($course->getCollegueFullName()) . '</span>';
                        }

                        echo '</div>
                </a>
            </li>';
        }
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

    function generateCalendar() {
        global $date, $realDate, $currentColors;
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

            $buttonClass = 'rounded-tl-lg bg-black-50 py-1.5 focus:z-10';
            if ($d == $actualDay) {
                $buttonClass = 'rounded-full border-2 border-sky-700 bg-black-50 py-1.5 focus:z-10';
            } elseif ($cMonth != $month) {
                $buttonClass = 'rounded-tl-lg bg-black-50 py-1.5 ' . $currentColors['unfocustext'] . ' focus:z-10';
            }

            // Calculer l'offset de la semaine par rapport à la semaine actuelle
            $weekOffset = (new DateTime($d))->diff(new DateTime($actualDay))->days / 7;
            if ($d < $actualDay) {
                $weekOffset = -ceil($weekOffset);
            } else {
                $weekOffset = floor($weekOffset);
            }

            echo '<form action="Dashboard.php" method="POST" style="display:inline;">
            <input type="hidden" name="weekOffSet" value="' . $weekOffset . '">
            <button type="submit" class="' . $buttonClass . '">
                <time class="mx-auto flex h-7 w-7 items-center justify-center rounded-full">' . $day . '</time>
            </button>
        </form>';
        }
    }

    //La fonction getWeekDay renvoie la date soit du lundi, soit du dimanche de la semaine en cours, en fonction du paramètre firstDay.
    function getWeekDay($firstDay) {
        // Données de bases
        setlocale(LC_TIME, 'fr_FR.UTF-8');
        $realDate = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $week = clone $realDate;
        $week = $week->modify(($_SESSION['weekOffSet'] * 7) . ' days');
        $resultDate = clone $week;

        if ($firstDay) {
            $resultDate->modify('monday this week');
        } else {
            $resultDate->modify('sunday this week');
        }

        return $resultDate;
    }

    //La fonction getDayWeek renvoie la date d'un jour spécifique de la semaine en cours. Elle prend un jour en paramètre et modifie la date de la semaine clonée pour correspondre au jour spécifié
    function getDayWeek($day) {
        // Données de bases
        setlocale(LC_TIME, 'fr_FR.UTF-8');
        $realDate = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $week = clone $realDate;
        $week = $week->modify(($_SESSION['weekOffSet'] * 7) . ' days');
        $resultDate = clone $week;
        $resultDate->modify($day . ' this week');

        return $resultDate;
    }

}
