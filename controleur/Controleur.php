<?php
include "../modele/CollegueManager.php";
include "../modele/EnseignementManager.php";
include "../modele/ScheduleManager.php";


class Controleur
{
    public function __construct() {

    }

    function generateDays($week) {
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
                <a class="group absolute inset-1 flex flex-col overflow-y-auto rounded-lg bg-'. $color . '-50 p-2 text-xs leading-5 hover:bg-' . $color . '-100">
                <form>
                <button">
                    <p class="text-'. $color . '-500 font-semibold group-hover:text-'. $color . '-700"><time>'. $dispHour . ':' . $dispMinute . ' - ' . ($course->getSalle() == '' ? 'Pas de salle' : ($course->getSalle() == '200' ? 'Amphi.' : 'Salle ' . $course->getSalle())) . '</time></p>
                    <p class="order-1 text-'. $color . '-700">' . $course->getTypeseance() . ' - ' . getEnseignementShortName($course->getCode()) . '</p>
                    <p class="order-1 text-'. $color . '-700">' . transformTeacherName(getCollegueFullName($course->getCollegue())) . '</p>
                </button>
              </form>
              </a>';
            }
        }
    }

}