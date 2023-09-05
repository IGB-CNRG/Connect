<?php

namespace App\Tests\Entity;

use App\Entity\Building;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class HistoricalEntityTest extends TestCase
{
    public function testCurrent(): void
    {
        $building = new Building(); // Building implements HistoricalEntity

        // Test isCurrent
        $this->assertTrue($building->isCurrent(), 'No date information should result in a "current" entity');
        $building->setStartedAt(new DateTime('next week'));
        $this->assertFalse($building->isCurrent(), 'Start date in the future should result in a non-current entity');
        $building->setStartedAt(new DateTime('2 days ago'));
        $this->assertTrue($building->isCurrent(), 'Start date in the past should result in a "current" entity');
        $building->setEndedAt(new DateTime('next week'));
        $this->assertTrue($building->isCurrent(), 'End date in the future should result in a "current" entity');
        $building->setEndedAt(new DateTime('1 day ago'));
        $this->assertFalse($building->isCurrent(), 'End date in the past should result in a non-current entity');
        $building->setStartedAt(new DateTime('today'))
            ->setEndedAt(new DateTime('next week'));
        $this->assertTrue($building->isCurrent(), 'Start date today should result in a current entity');
        $building->setStartedAt(new DateTime('1 week ago'))
            ->setEndedAt(new DateTime('today'));
        $this->assertTrue($building->isCurrent(), 'End date today should result in a current entity');
    }

    public function testWasCurrentOnDate()
    {
        $building = new Building();

        $someDate = new DateTime('8/23/23');
        $building->setStartedAt($someDate);
        $this->assertTrue($building->wasCurrentAtDate($someDate), 'Entities should be current on the day they begin');
        $building->setStartedAt(null)->setEndedAt($someDate);
        $this->assertTrue($building->wasCurrentAtDate($someDate), 'Entities should be current on the day they end');
    }

    public function testOverlap()
    {
        // Test overlap
        $building1 = new Building();
        $building2 = new Building();

        $fourWeeks = new DateTimeImmutable('4 weeks ago');
        $threeWeeks = new DateTimeImmutable('3 weeks ago');
        $twoWeeks = new DateTimeImmutable('2 weeks ago');
        $tenDays = new DateTimeImmutable('10 days ago');
        $eightDays = new DateTimeImmutable('8 days ago');
        $oneWeek = new DateTimeImmutable('1 week ago');
        $fiveDays = new DateTimeImmutable('5 days ago');
        $threeDays = new DateTimeImmutable('3 days ago');

        $building1->setStartedAt($twoWeeks)
            ->setEndedAt($oneWeek);

        //         |----|
        //                |----|
        $building2->setStartedAt($fiveDays)->setEndedAt($threeDays);
        $this->assertFalse($building1->overlaps($building2));

        //         |----|
        //  |----|
        $building2->setStartedAt($fourWeeks)->setEndedAt($threeWeeks);
        $this->assertFalse($building1->overlaps($building2));

        //         |----|
        //          |--|
        $building2->setStartedAt($tenDays)->setEndedAt($eightDays);
        $this->assertTrue($building1->overlaps($building2));

        //         |----|
        //            |----|
        $building2->setStartedAt($eightDays)->setEndedAt($fiveDays);
        $this->assertTrue($building1->overlaps($building2));

        //         |----|
        //      |----|
        $building2->setStartedAt($fourWeeks)->setEndedAt($eightDays);
        $this->assertTrue($building1->overlaps($building2));

        //         |----|
        //      |----------|
        $building2->setStartedAt($fourWeeks)->setEndedAt($threeDays);
        $this->assertTrue($building1->overlaps($building2));

        //         |----|
        //                |----
        $building2->setStartedAt($fiveDays)->setEndedAt(null);
        $this->assertFalse($building1->overlaps($building2));

        //         |----|
        //  ----|
        $building2->setStartedAt(null)->setEndedAt($threeWeeks);
        $this->assertFalse($building1->overlaps($building2));

        //         |----|
        //            |----
        $building2->setStartedAt($eightDays)->setEndedAt(null);
        $this->assertTrue($building1->overlaps($building2));

        //         |----|
        //  ---------|
        $building2->setStartedAt(null)->setEndedAt($eightDays);
        $this->assertTrue($building1->overlaps($building2));

        //         |----|
        //  --------------|
        $building2->setStartedAt(null)->setEndedAt($fiveDays);
        $this->assertTrue($building1->overlaps($building2));

        //         |----|
        //  |---------------
        $building2->setStartedAt($fourWeeks)->setEndedAt(null);
        $this->assertTrue($building1->overlaps($building2));

        //         |----|
        //  -----------------
        $building2->setStartedAt(null)->setEndedAt(null);
        $this->assertTrue($building1->overlaps($building2));

        // No end date
        $building1->setStartedAt($twoWeeks)->setEndedAt(null);

        //         |-------------
        //            |----|
        $building2->setStartedAt($fiveDays)->setEndedAt($threeDays);
        $this->assertTrue($building1->overlaps($building2));

        //         |-------------
        //       |----|
        $building2->setStartedAt($threeWeeks)->setEndedAt($threeDays);
        $this->assertTrue($building1->overlaps($building2));

        //         |-------------
        //  |----|
        $building2->setStartedAt($fourWeeks)->setEndedAt($threeWeeks);
        $this->assertFalse($building1->overlaps($building2));

        //         |-------------
        //            |----------
        $building2->setStartedAt($fiveDays)->setEndedAt(null);
        $this->assertTrue($building1->overlaps($building2));

        //         |-------------
        //      |----------------
        $building2->setStartedAt($threeWeeks)->setEndedAt(null);
        $this->assertTrue($building1->overlaps($building2));

        //         |-------------
        //  ---------|
        $building2->setStartedAt(null)->setEndedAt($threeDays);
        $this->assertTrue($building1->overlaps($building2));

        //         |-------------
        //  ----|
        $building2->setStartedAt(null)->setEndedAt($threeWeeks);
        $this->assertFalse($building1->overlaps($building2));

        //         |-------------
        //  ---------------------
        $building2->setStartedAt(null)->setEndedAt(null);
        $this->assertTrue($building1->overlaps($building2));

        // No start date
        $building1->setStartedAt(null)->setEndedAt($oneWeek);

        //  -------|
        //            |----|
        $building2->setStartedAt($fiveDays)->setEndedAt($threeDays);
        $this->assertFalse($building1->overlaps($building2));

        //  -------|
        //       |----|
        $building2->setStartedAt($threeWeeks)->setEndedAt($threeDays);
        $this->assertTrue($building1->overlaps($building2));

        //  -------|
        //  |----|
        $building2->setStartedAt($fourWeeks)->setEndedAt($threeWeeks);
        $this->assertTrue($building1->overlaps($building2));

        //  -------|
        //            |----------
        $building2->setStartedAt($fiveDays)->setEndedAt(null);
        $this->assertFalse($building1->overlaps($building2));

        //  -------|
        //      |----------------
        $building2->setStartedAt($threeWeeks)->setEndedAt(null);
        $this->assertTrue($building1->overlaps($building2));

        //  -------|
        //  ---------|
        $building2->setStartedAt(null)->setEndedAt($threeDays);
        $this->assertTrue($building1->overlaps($building2));

        //  -------|
        //  ----|
        $building2->setStartedAt(null)->setEndedAt($threeWeeks);
        $this->assertTrue($building1->overlaps($building2));

        //  -------|
        //  ---------------------
        $building2->setStartedAt(null)->setEndedAt(null);
        $this->assertTrue($building1->overlaps($building2));

        // No dates
        $building1->setStartedAt(null)->setEndedAt(null);

        // ----------------------
        //     |------|
        $building2->setStartedAt($twoWeeks)->setEndedAt($oneWeek);
        $this->assertTrue($building1->overlaps($building2));

        // ----------------------
        // ------|
        $building2->setStartedAt(null)->setEndedAt($oneWeek);
        $this->assertTrue($building1->overlaps($building2));

        // ----------------------
        //     |-----------------
        $building2->setStartedAt($twoWeeks)->setEndedAt(null);
        $this->assertTrue($building1->overlaps($building2));

        // ----------------------
        // ----------------------
        $building2->setStartedAt(null)->setEndedAt(null);
        $this->assertTrue($building1->overlaps($building2));
    }
}
