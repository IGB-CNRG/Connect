<?php

namespace App\Tests\Service;

use App\Entity\RoomAffiliation;
use App\Entity\ThemeAffiliation;
use App\Service\HistoricityManager;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class HistoricityManagerTest extends KernelTestCase
{
    private ?HistoricityManager $historicityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->historicityManager = $this->getContainer()->get(HistoricityManager::class);
    }

    public function testEndAffiliations()
    {
        $fourWeeks = new DateTimeImmutable('4 weeks ago');
        $threeWeeks = new DateTimeImmutable('3 weeks ago');
        $twoWeeks = new DateTimeImmutable('2 weeks ago');
        $oneWeek = new DateTimeImmutable('1 week ago');

        $exitReason = "Test reason";
        $differentExitReason = "Another test reason";

        $da1 = (new RoomAffiliation())
            ->setStartedAt($fourWeeks);
        $da2 = (new RoomAffiliation())
            ->setStartedAt($fourWeeks)
            ->setEndedAt($threeWeeks);
        $da3 = (new RoomAffiliation())
            ->setStartedAt($fourWeeks)
            ->setEndedAt($oneWeek);
        $ta1 = (new ThemeAffiliation())
            ->setStartedAt($fourWeeks);
        $ta2 = (new ThemeAffiliation())
            ->setStartedAt($fourWeeks)
            ->setEndedAt($threeWeeks)
            ->setExitReason($differentExitReason);
        $ta3 = (new ThemeAffiliation())
            ->setStartedAt($fourWeeks)
            ->setEndedAt($oneWeek)
            ->setExitReason($differentExitReason);

        $this->historicityManager->endAffiliations(
            [$da1, $da2, $da3, $ta1, $ta2, $ta3],
            $twoWeeks,
            $exitReason
        );

        $this->assertEquals($fourWeeks, $da1->getStartedAt());
        $this->assertEquals($fourWeeks, $da2->getStartedAt());
        $this->assertEquals($fourWeeks, $da3->getStartedAt());
        $this->assertEquals($fourWeeks, $ta1->getStartedAt());
        $this->assertEquals($fourWeeks, $ta2->getStartedAt());
        $this->assertEquals($fourWeeks, $ta3->getStartedAt());

        $this->assertEquals($twoWeeks, $da1->getEndedAt());
        $this->assertEquals($threeWeeks, $da2->getEndedAt());
        $this->assertEquals($twoWeeks, $da3->getEndedAt());
        $this->assertEquals($twoWeeks, $ta1->getEndedAt());
        $this->assertEquals($threeWeeks, $ta2->getEndedAt());
        $this->assertEquals($twoWeeks, $ta3->getEndedAt());
        $this->assertEquals($exitReason, $ta1->getExitReason());
        $this->assertEquals($differentExitReason, $ta2->getExitReason());
        $this->assertEquals($exitReason, $ta3->getExitReason());
    }
}