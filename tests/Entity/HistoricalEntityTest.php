<?php

namespace App\Tests\Entity;

use App\Entity\Building;
use PHPUnit\Framework\TestCase;

class HistoricalEntityTest extends TestCase
{
    public function testSomething(): void
    {
        $building = new Building(); // Building implements HistoricalEntity

        $this->assertTrue($building->isCurrent(), 'No date information should result in a "current" entity');
        $building->setStartedAt(new \DateTime('next week'));
        $this->assertFalse($building->isCurrent(), 'Start date in the future should result in a non-current entity');
        $building->setStartedAt(new \DateTime('2 days ago'));
        $this->assertTrue($building->isCurrent(), 'Start date in the past should result in a "current" entity');
        $building->setEndedAt(new \DateTime('next week'));
        $this->assertTrue($building->isCurrent(), 'End date in the future should result in a "current" entity');
        $building->setEndedAt(new \DateTime('1 day ago'));
        $this->assertFalse($building->isCurrent(), 'End date in the past should result in a non-current entity');
    }
}
