<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
class DashboardTest extends TestCase
{
    public function testGetDataReturnsNonEmptyArray(): void
    {
        $dashboard = new Dashboard();
        $data = $dashboard->getData();

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }
}
