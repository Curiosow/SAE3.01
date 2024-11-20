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


                $uniqueId = uniqid(); // Génère un identifiant unique

                echo '<li class="relative mt-px flex sm:col-start-' . $dispHoraire . '" style="grid-row: ' . $dispGridRow . ' / span ' . $dispSpan . '">
    <a class="group absolute inset-1 flex flex-col overflow-visible rounded-lg bg-' . $color . '-50 p-2 text-sm leading-5 hover:bg-' . $color . '-100">
        <form>
            <div>
                <p class="text-' . $color . '-500 font-semibold group-hover:text-' . $color . '-700">
                    <time>' . $dispHour . ':' . $dispMinute . ' - ' . ($course->getSalle() == '' ? 'Pas de salle' : ($course->getSalle() == '200' ? 'Amphi.' : 'Salle ' . $course->getSalle())) . '</time>
                </p>
                <p class="order-1 text-' . $color . '-700">' . $course->getTypeseance() . ' - ' . getEnseignementShortName($course->getCode()) . '</p>
                <p class="order-1 text-' . $color . '-700">' . transformTeacherName(getCollegueFullName($course->getCollegue())) . '</p>
            </div>
        </form>

        <!-- Bouton pour afficher l\'info-bulle -->
        <button data-tooltip-target="tooltip-' . $uniqueId . '"
                class="select-none rounded-lg bg-transparent py-1 px-2 text-xs font-bold uppercase text-gray-500 hover:text-gray-700 focus:outline-none"
                style="position: absolute; top: 0; right: 0;">
            ⓘ
        </button>

        <!-- Info-bulle avec animation -->
        <div id="tooltip-' . $uniqueId . '"
             data-tooltip="tooltip-' . $uniqueId . '"
             class="hidden absolute z-50 whitespace-normal break-words rounded-lg bg-white py-1.5 px-3 font-sans text-sm font-normal text-black focus:outline-none transition-opacity duration-200 ease-in-out" style="width: 200px; right: -210px; top: 0;">
            <p class="text-center font-bold text-lg">' . $course->getTypeseance() . '</p>
            <p class="text-purple-500">' . getEnseignementShortName($course->getCode()) . '</p>
            <p class="text-blue-500">' . $dispHour . ':' . $dispMinute . '</p>
            <p class="text-green-500">' . ($course->getSalle() == '' ? 'Pas de salle' : ($course->getSalle() == '200' ? 'Amphi.' : 'Salle ' . $course->getSalle())) . '</p>
            <p class="text-red-500">' . $course->getNomgroupe() . '</p>
            <p class="text-orange-500">' . transformTeacherName(getCollegueFullName($course->getCollegue())) . '</p>
        </div>
    </a>
</li>';
?>


<script
  type="module"
  src="https://unpkg.com/@material-tailwind/html@latest/scripts/tooltip.js"
></script>


<script>
// JavaScript pour afficher/masquer les info-bulles
document.querySelectorAll('[data-tooltip-target]').forEach(button => {
    button.addEventListener('mouseenter', () => {
        const tooltipId = button.getAttribute('data-tooltip-target');
        const tooltip = document.getElementById(tooltipId);
        if (tooltip) tooltip.classList.remove('hidden');
    });

    button.addEventListener('mouseleave', () => {
        const tooltipId = button.getAttribute('data-tooltip-target');
        const tooltip = document.getElementById(tooltipId);
        if (tooltip) tooltip.classList.add('hidden');
    });
});
</script>
<?php
            }
        }
    }

    function returnVersion() {
        return getVersion();
    }

}
