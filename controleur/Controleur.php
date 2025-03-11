<?php
include_once "../modele/managers/CollegueManager.php";
include_once "../modele/managers/EnseignementManager.php";
include_once "../modele/managers/ScheduleManager.php";


class Controleur
{
    public function __construct() {

    }

    function generateDays($week, $previousVersion = false, $teacherEdt = false) {
        $weekDates = getWeekDates($week);

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
                $dispGridRow = getGridRow($horraire);

                $duree = new DateTime($course->getDuration(), new DateTimeZone('Europe/Paris'));
                $dispSpan = max(getSpan($duree), ceil(strlen($course->getTypeseance()) / 20) + 1);



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

                echo '<li class="relative mt-px flex col-start-' . $dispHoraire . ' sm:col-start-' . $dispHoraire . '" style="grid-row: ' . $dispGridRow . ' / span ' . $dispSpan . '">
    <a class="group absolute inset-1 flex flex-col overflow-visible rounded-lg bg-' . $color . '-50 p-2 text-sm leading-5 hover:bg-' . $color . '-100">
        <form>
            <div>
                <p class="text-' . $color . '-500 font-semibold group-hover:text-' . $color . '-700">
                    <time>' . $dispHour . ':' . $dispMinute . ' - ' . ($course->getSalle() == '' ? 'Pas de salle' : ($course->getSalle() == '200' ? 'Amphi.' : 'Salle ' . $course->getSalle())) . $exam . '</time>
                </p>
                <p class="order-1 text-' . $color . '-700">' . $course->getTypeseance() . ' - ' . $course->getEnseignementShortName() . '</p>
                <p class="order-1 text-' . $color . '-700">' . $this->transformTeacherName($course->getCollegueFullName()) . '</p>
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
             class="absolute z-50 whitespace-normal break-words rounded-lg bg-gray-50 py-1.5 px-3 font-sans text-sm font-normal text-black focus:outline-none transition-opacity opacity-0 duration-200 ease-in-out border border-black" style="width: 200px; right: -210px; top: 0;">
            <p class="text-center font-bold text-lg">' . $course->getTypeseance() . '</p>
            <span>Cours : </span><span class="text-purple-500">' . $course->getEnseignementLongName() . '</span><br>
            <span>Horaire : </span><span class="text-blue-500">' . $dispHour . ':' . $dispMinute . '</span><br>
            <span>Salle : </span><span class="text-green-500">' . ($course->getSalle() == '' ? 'Pas de salle' : ($course->getSalle() == '200' ? 'Amphi.' : 'Salle ' . $course->getSalle())) . '</span><br>
            <span>Groupe : </span><span class="text-red-500">' . $course->getNomgroupe() . '</span><br>            
            ';

                if($course->getCollegue() != '' && $course->getCollegue() != null) {
                    echo'
            <span>Prof : </span> <span class="text-orange-500">' . $this->transformTeacherName($course->getCollegueFullName()) . '</span>';
                }

                echo '</div>
            
    </a>
</li>';
?>
<?php
            }
        }
    }

    function generateDay($day, $previousVersion = false, $teacherEdt = false) {
        $disciplineColors = getDisciplineColors();

        if (!$previousVersion) {
            $courses = getDay($day, $day->format('d'), $_COOKIE['semestre'], $_COOKIE['groupe'], (int) $_COOKIE['sousgroupe'], $_COOKIE['formation'], $teacherEdt);
        } else {
            $courses = getDayPreviousVersion($day, $day->format('d'), $_COOKIE['semestre'], $_COOKIE['groupe'], (int) $_COOKIE['sousgroupe'], $_COOKIE['formation'], $teacherEdt);
        }

        $alreadyPlace = [];
        foreach ($courses as $course) {
            if (in_array($course, $alreadyPlace))
                continue;

            $alreadyPlace[] = $course;

            $horraire = new DateTime($course->getHoraire(), new DateTimeZone('Europe/Paris'));
            $dispGridRow = getGridRow($horraire);

            $duree = new DateTime($course->getDuration(), new DateTimeZone('Europe/Paris'));
            $dispSpan = getSpan($duree);

            $color = "gray";
            if (array_key_exists($course->getDiscipline(), $disciplineColors))
                $color = $disciplineColors[$course->getDiscipline()];

            $dispHour = (int)$horraire->format("H");
            if ($dispHour < 10 && $dispHour > 1)
                $dispHour = '0' . $dispHour;

            $dispMinute = $horraire->format("i") . '';
            if ($horraire->format("i") < 10 && $horraire->format("i") > 1)
                $dispMinute = '0' . $dispMinute;

            $uniqueId = uniqid(); // Génère un identifiant unique

            $exam = "";
            if ($course->getExam() == 't') {
                $exam = " - EXAMEN";
            }

            echo '<li class="relative mt-px flex sm:col-start-1 sm:col-end-6" style="grid-row: ' . $dispGridRow . ' / span ' . $dispSpan . '">
            <a class="group absolute inset-1 flex flex-col overflow-visible rounded-lg bg-' . $color . '-50 p-2 text-sm leading-5 hover:bg-' . $color . '-100">
                <form>
                    <div>
                        <p class="text-' . $color . '-500 font-semibold group-hover:text-' . $color . '-700">
                            <time>' . $dispHour . ':' . $dispMinute . ' - ' . ($course->getSalle() == '' ? 'Pas de salle' : ($course->getSalle() == '200' ? 'Amphi.' : 'Salle ' . $course->getSalle())) . $exam . '</time>
                        </p>
                        <p class="order-1 text-' . $color . '-700">' . $course->getTypeseance() . ' - ' . $course->getEnseignementShortName() . '</p>
                        <p class="order-1 text-' . $color . '-700">' . $this->transformTeacherName($course->getCollegueFullName()) . '</p>
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
                     class="absolute z-50 whitespace-normal break-words rounded-lg bg-gray-50 py-1.5 px-3 font-sans text-sm font-normal text-black focus:outline-none transition-opacity opacity-0 duration-200 ease-in-out border border-black" style="width: 200px; right: -210px; top: 0;">
                    <p class="text-center font-bold text-lg">' . $course->getTypeseance() . '</p>
                    <span>Cours : </span><span class="text-purple-500">' . $course->getEnseignementLongName() . '</span><br>
                    <span>Horaire : </span><span class="text-blue-500">' . $dispHour . ':' . $dispMinute . '</span><br>
                    <span>Salle : </span><span class="text-green-500">' . ($course->getSalle() == '' ? 'Pas de salle' : ($course->getSalle() == '200' ? 'Amphi.' : 'Salle ' . $course->getSalle())) . '</span><br>
                    <span>Groupe : </span><span class="text-red-500">' . $course->getNomgroupe() . '</span><br>
                    ';

            if ($course->getCollegue() != '' && $course->getCollegue() != null) {
                echo'
                    <span>Prof : </span> <span class="text-orange-500">' . $this->transformTeacherName($course->getCollegueFullName()) . '</span>';
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

}
