<?php

namespace App\Tests\Repository;

use App\Entity\Person;
use App\Repository\PersonRepository;
use App\Tests\Utils\DatabaseTestCase;

class PersonRepositoryTest extends DatabaseTestCase
{
    private ?PersonRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = PersonRepositoryTest::getContainer()->get(PersonRepository::class);
    }

    public function testFindAllNeedingApproval(){
        $this->assertEmpty($this->repository->findAllNeedingApproval()); // Nothing in the database, should return nothing
        
        $this->insertTestPeople();
        $needApproval = $this->repository->findAllNeedingApproval();

        $this->assertIsArray($needApproval);
        $this->assertCount(2, $needApproval);
        $this->assertEquals('test2', $needApproval[0]->getUsername());
        $this->assertEquals('test4', $needApproval[1]->getUsername());
    }

    private function insertTestPeople(): void
    {
        $now = new \DateTimeImmutable();
        $test1 = (new Person())
            ->setUsername('test1')
            ->setLastName('A')
            ->setMembershipStatus('need_entry_form')
            ->setMembershipUpdatedAt($now);
        $this->entityManager->persist($test1);

        $test2 = (new Person())
            ->setUsername('test2')
            ->setLastName('B')
            ->setMembershipStatus('entry_form_submitted')
            ->setMembershipUpdatedAt($now);
        $this->entityManager->persist($test2);

        $test3 = (new Person())
            ->setUsername('test3')
            ->setLastName('C')
            ->setMembershipStatus('need_certificates')
            ->setMembershipUpdatedAt($now);
        $this->entityManager->persist($test3);

        $test4 = (new Person())
            ->setUsername('test4')
            ->setLastName('D')
            ->setMembershipStatus('certificates_submitted')
            ->setMembershipUpdatedAt($now);
        $this->entityManager->persist($test4);

        $test5 = (new Person())
            ->setUsername('test5')
            ->setLastName('E')
            ->setMembershipStatus('active')
            ->setMembershipUpdatedAt($now);
        $this->entityManager->persist($test5);

        $this->entityManager->flush();
    }
}