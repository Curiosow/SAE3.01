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


                $uniqueId = uniqid(); // Génère un identifiant unique pour chaque bloc

                echo '<li class="relative mt-px flex sm:col-start-' . $dispHoraire . '" style="grid-row: ' . $dispGridRow . ' / span ' . $dispSpan . '">
    <a class="group absolute inset-1 flex flex-col overflow-y-auto rounded-lg bg-' . $color . '-50 p-2 text-sm leading-5 hover:bg-' . $color . '-100" style="overflow: visible;">
        <form>
            <div>
                <p class="text-' . $color . '-500 font-semibold group-hover:text-' . $color . '-700">
                    <time>' . $dispHour . ':' . $dispMinute . ' - ' .
                    (empty($course->getSalle()) ? 'Pas de salle' : ($course->getSalle() == '200' ? 'Amphi.' : 'Salle ' . $course->getSalle())) .
                    '</time>
                </p>
                <p class="order-1 text-' . $color . '-700">' . $course->getTypeseance() . ' - ' . getEnseignementShortName($course->getCode()) . '</p>
                <p class="order-1 text-' . $color . '-700">' . transformTeacherName(getCollegueFullName($course->getCollegue())) . '</p>
            </div>
        </form>
        
        <!-- Bouton pour afficher linfo-bulle avec animation -->
        <button data-ripple-light="true" data-tooltip-target="tooltip-' . $uniqueId . '"
                class="select-none absolute top-0 right-0 m-2 rounded-lg bg-transparent py-1 px-2 text-xs font-bold uppercase text-gray-500 hover:text-gray-700 focus:outline-none">
                ⓘ
        </button>
        
        <!-- Info-bulle avec animation et taille fixe pour éviter le redimensionnement -->
        <div id="tooltip-' . $uniqueId . '" 
             data-tooltip="tooltip-' . $uniqueId . '" 
             data-tooltip-mount="opacity-100 scale-100"
             data-tooltip-unmount="opacity-0 scale-0 pointer-events-none"
             data-tooltip-transition="transition-all duration-200 origin-bottom"
             class="absolute z-50 rounded-lg bg-black py-2 px-3 font-sans text-sm font-normal text-white focus:outline-none"
             style="top: -10px; right: 0; min-width: 150px; max-width: 200px; white-space: normal;">
                   Test
                </div>
    </a>
</li>';
?>

<!-- Import Material Tailwind tooltip script -->
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
