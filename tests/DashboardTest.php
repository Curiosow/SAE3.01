<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class DashboardTest extends TestCase
{
    public function testGetDayWeek()
    {
        // Assuming getDayWeek is a function in Dashboard.php
        $monday = getDayWeek('monday');
        $this->assertInstanceOf(\DateTime::class, $monday);
        $this->assertEquals('Monday', $monday->format('l'));

        $tuesday = getDayWeek('tuesday');
        $this->assertInstanceOf(\DateTime::class, $tuesday);
        $this->assertEquals('Tuesday', $tuesday->format('l'));
    }

    public function testGenerateDays()
    {
        // Assuming generateDays is a method in a controller used in Dashboard.php
        $controller = new \Controleur();
        $week = getWeekDates(new \DateTime());
        ob_start();
        $controller->generateDays($week);
        $output = ob_get_clean();

        $this->assertStringContainsString('data-date', $output);
        $this->assertStringContainsString('onclick="handleDayClicked(this)"', $output);
    }
}