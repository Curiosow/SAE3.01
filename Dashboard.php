<?php
session_start();

setlocale(LC_TIME, 'fr_FR.UTF-8');
$date = new DateTime('now', new DateTimeZone('Europe/Paris'));

if (isset($_POST['monthOffSet'])) {
    $_SESSION['monthOffSet'] = (int)$_POST['monthOffSet'];
} else {
    if (!isset($_SESSION['monthOffSet'])) {
        $_SESSION['monthOffSet'] = 0;
    }
}

$date->modify($_SESSION['monthOffSet'] . ' month');
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
    <!-- Static sidebar -->
    <div class="lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
        <!-- Sidebar component-->
        <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-black px-6 pb-4">
            <div class="mt-10 text-center lg:col-start-8 lg:col-end-13 lg:row-start-1 lg:mt-9 xl:col-start-9">
                <div class="flex items-center text-gray-400">
                    <form action="Dashboard.php" method="post" class="flex w-full">
                        <button type="submit" name="monthOffSet" value="<?php echo ($_SESSION['monthOffSet'] - 1); ?>" class="-m-1.5 flex flex-none items-center justify-center p-1.5 text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Previous month</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div class="flex-auto text-sm font-semibold"><?php echo ucfirst($month); ?></div>
                        <button type="submit" name="monthOffSet" value="<?php echo ($_SESSION['monthOffSet'] + 1); ?>" class="-m-1.5 flex flex-none items-center justify-center p-1.5 text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Next month</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </form>

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
            </div>

            <div class="mt-auto flex-col justify-center ">
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
            <header class="flex justify-center items-center border-b border-gray-200 px-4 py-2">
                <div class="flex flex-center items-center rounded-md bg-white shadow-sm md:items-stretch">
                    <button type="button" class="flex h-9 w-12 items-center justify-center rounded-l-md border-y border-l border-gray-300 pr-1 text-gray-400 hover:text-gray-500 focus:relative md:w-9 md:pr-0 md:hover:bg-gray-50">
                        <span class="sr-only">Previous week</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button type="button" class="hidden border-y border-gray-300 px-3.5 text-sm font-semibold text-gray-900 hover:bg-gray-50 focus:relative md:block">Du . au .</button>
                    <span class="relative -mx-px h-5 w-px bg-gray-300 md:hidden"></span>
                    <button type="button" class="flex h-9 w-12 items-center justify-center rounded-r-md border-y border-r border-gray-300 pl-1 text-gray-400 hover:text-gray-500 focus:relative md:w-9 md:pl-0 md:hover:bg-gray-50">
                        <span class="sr-only">Next week</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </header>

            <div class="isolate flex flex-auto flex-col overflow-auto bg-white">
                <div class="flex max-w-full flex-none flex-col sm:max-w-none md:max-w-full">
                    <div class="sticky top-0 z-30 flex-none bg-white shadow ring-1 ring-black ring-opacity-5 sm:pr-8">
                        <div class="grid grid-cols-7 text-sm leading-6 text-gray-500 sm:hidden">
                            <button type="button" class="flex flex-col items-center pb-3 pt-2">M <span class="mt-1 flex h-8 w-8 items-center justify-center font-semibold text-gray-900">10</span></button>
                            <button type="button" class="flex flex-col items-center pb-3 pt-2">T <span class="mt-1 flex h-8 w-8 items-center justify-center font-semibold text-gray-900">11</span></button>
                            <button type="button" class="flex flex-col items-center pb-3 pt-2">W <span class="mt-1 flex h-8 w-8 items-center justify-center font-semibold text-white">12</span></button>
                            <button type="button" class="flex flex-col items-center pb-3 pt-2">T <span class="mt-1 flex h-8 w-8 items-center justify-center font-semibold text-gray-900">13</span></button>
                            <button type="button" class="flex flex-col items-center pb-3 pt-2">F <span class="mt-1 flex h-8 w-8 items-center justify-center font-semibold text-gray-900">14</span></button>
                            <button type="button" class="flex flex-col items-center pb-3 pt-2">S <span class="mt-1 flex h-8 w-8 items-center justify-center font-semibold text-gray-900">15</span></button>
                            <button type="button" class="flex flex-col items-center pb-3 pt-2">S <span class="mt-1 flex h-8 w-8 items-center justify-center font-semibold text-gray-900">16</span></button>
                        </div>

                        <div class="-mr-px hidden grid-cols-7 divide-x divide-gray-100 border-r border-gray-100 text-sm leading-6 text-gray-500 sm:grid">
                            <div class="col-end-1 w-14"></div>
                            <div class="flex items-center justify-center py-3">
                                <span>Lun <span class="items-center justify-center font-semibold text-gray-900">10</span></span>
                            </div>
                            <div class="flex items-center justify-center py-3">
                                <span>Mar <span class="items-center justify-center font-semibold text-gray-900">11</span></span>
                            </div>
                            <div class="flex items-center justify-center py-3">
                                <span>Mer <span class="items-center justify-center font-semibold text-gray-900">12</span></span>
                            </div>
                            <div class="flex items-center justify-center py-3">
                                <span>Jeu <span class="items-center justify-center font-semibold text-gray-900">13</span></span>
                            </div>
                            <div class="flex items-center justify-center py-3">
                                <span>Ven <span class="items-center justify-center font-semibold text-gray-900">14</span></span>
                            </div>
                            <div class="flex items-center justify-center py-3">
                                <span>Sam <span class="items-center justify-center font-semibold text-gray-900">15</span></span>
                            </div>
                            <div class="flex items-center justify-center py-3">
                                <span> Dim <span class="items-center justify-center font-semibold text-gray-900">16</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-auto">
                        <div class="sticky left-0 z-10 w-14 flex-none bg-white ring-1 ring-gray-100"></div>
                        <div class="grid flex-auto grid-cols-1 grid-rows-1">
                            <!-- Horizontal lines -->
                            <div class="col-start-1 col-end-2 row-start-1 grid divide-y divide-gray-100" style="grid-template-rows : repeat(21, minmax(4vh, 1fr));">
                                <!-- Adjust top margin for mobile -->
                                <div class="row-end-1 h-7"></div>
                                <div>
                                    <div class="sticky left-0 z-20 -ml-14 -mt-2.5 w-14 pr-2 text-right text-xs leading-5 text-gray-400">8</div>
                                </div>
                                <div></div>
                                <div>
                                    <div class="sticky left-0 z-20 -ml-14 -mt-2.5 w-14 pr-2 text-right text-xs leading-5 text-gray-400">9</div>
                                </div>
                                <div></div>
                                <div>
                                    <div class="sticky left-0 z-20 -ml-14 -mt-2.5 w-14 pr-2 text-right text-xs leading-5 text-gray-400">10</div>
                                </div>
                                <div></div>
                                <div>
                                    <div class="sticky left-0 z-20 -ml-14 -mt-2.5 w-14 pr-2 text-right text-xs leading-5 text-gray-400">11</div>
                                </div>
                                <div></div>
                                <div>
                                    <div class="sticky left-0 z-20 -ml-14 -mt-2.5 w-14 pr-2 text-right text-xs leading-5 text-gray-400">12</div>
                                </div>
                                <div></div>
                                <div>
                                    <div class="sticky left-0 z-20 -ml-14 -mt-2.5 w-14 pr-2 text-right text-xs leading-5 text-gray-400">13</div>
                                </div>
                                <div></div>
                                <div>
                                    <div class="sticky left-0 z-20 -ml-14 -mt-2.5 w-14 pr-2 text-right text-xs leading-5 text-gray-400">14</div>
                                </div>
                                <div></div>
                                <div>
                                    <div class="sticky left-0 z-20 -ml-14 -mt-2.5 w-14 pr-2 text-right text-xs leading-5 text-gray-400">15</div>
                                </div>
                                <div></div>
                                <div>
                                    <div class="sticky left-0 z-20 -ml-14 -mt-2.5 w-14 pr-2 text-right text-xs leading-5 text-gray-400">16</div>
                                </div>
                                <div></div>
                                <div>
                                    <div class="sticky left-0 z-20 -ml-14 -mt-2.5 w-14 pr-2 text-right text-xs leading-5 text-gray-400">17</div>
                                </div>
                                <div></div>
                                <div>
                                    <div class="sticky left-0 z-20 -ml-14 -mt-2.5 w-14 pr-2 text-right text-xs leading-5 text-gray-400">18</div>
                                </div>
                                <div></div>
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
                </div>
            </div>
        </div>
    </div>
</body>
</html>