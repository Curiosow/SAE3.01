<?php

setlocale(LC_TIME, 'fr_FR.UTF-8');
$date = new DateTime('now', new DateTimeZone('Europe/Paris'));
$month = IntlDateFormatter::formatObject($date, 'MMMM y', 'fr');

function generateCalendar() {
    global $date;
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

    foreach ($calendar as $date) {
        $day = date('d', strtotime($date));
        echo '<button type="button" class="rounded-tl-lg bg-black-50 py-1.5 text-gray-400 focus:z-10">
                 <time class="mx-auto flex h-7 w-7 items-center justify-center rounded-full">'. $day . '</time>
               </button>';
    }
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

<div>
    <!-- Static sidebar -->
    <div class="lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
        <!-- Sidebar component-->
        <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-black px-6 pb-4">
            <div class="mt-10 text-center lg:col-start-8 lg:col-end-13 lg:row-start-1 lg:mt-9 xl:col-start-9">
                <div class="flex items-center text-gray-400">
                    <button type="button" class="-m-1.5 flex flex-none items-center justify-center p-1.5 text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Previous month</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div class="flex-auto text-sm font-semibold"><?php echo ucfirst($month); ?></div>
                    <button type="button" class="-m-1.5 flex flex-none items-center justify-center p-1.5 text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Next month</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div class="mt-6 grid grid-cols-7 text-xs leading-6 text-gray-400">
                    <div>Lun</div>
                    <div>Mar</div>
                    <div>Mer</div>
                    <div>Jeu</div>
                    <div>Ven</div>
                    <div>Sam</div>
                    <div>Dim</div>
                </div>
                <div class="isolate mt-2 grid grid-cols-7 gap-px rounded-lg bg-black text-sm shadow ring-1 ring-black">
                    <?php generateCalendar(); ?>
                </div>
                <footer>
                    <div class="mt-4 text-center text-gray-400">
                        <p>© 2023 Mon emploi du temps</p>
                    </div>
                </footer>

            </div>
        </div>

    </div>
    <div class="lg:pl-72">
        <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <!-- Dashboard -->

        </div>
    </div>

</div>



</body>
</html>