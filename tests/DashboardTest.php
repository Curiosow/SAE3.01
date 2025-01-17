<?php declare(strict_types=1);
include_once("../controleur/Controleur.php");

use PHPUnit\Framework\TestCase;


class DashboardTest extends TestCase
{
    public function testGenerateDays()
    {
        // Assuming you have a DashboardController class with a generateDays method
        $controller = new Controleur();

        // Mocking the necessary session variables
        $_SESSION['weekOffSet'] = 0;
        $_SESSION['semestre'] = 1;
        $_SESSION['groupe'] = 'A';
        $_SESSION['sousgroupe'] = 1;
        $_SESSION['formation'] = 'Engineering';

        // Mocking the week parameter
        $week = 1;

        // Start output buffering to capture the output of generateDays
        ob_start();
        $controller->generateDays($week);
        $output = ob_get_clean();

        // Assert that the output contains expected HTML elements
        $this->assertStringContainsString('<li class="relative mt-px flex sm:col-start-', $output);
        $this->assertStringContainsString('<a class="group absolute inset-1 flex flex-col overflow-visible rounded-lg bg-', $output);
    }
}