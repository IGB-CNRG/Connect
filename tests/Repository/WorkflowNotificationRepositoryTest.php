<?php

namespace App\Tests\Repository;

use App\Entity\MemberCategory;
use App\Entity\WorkflowNotification;
use App\Repository\WorkflowNotificationRepository;
use App\Tests\Utils\DatabaseTestCase;

class WorkflowNotificationRepositoryTest extends DatabaseTestCase
{
    private ?WorkflowNotificationRepository $repository;
    private MemberCategory $mc1;
    private MemberCategory $mc2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = WorkflowNotificationRepositoryTest::getContainer()->get(
            WorkflowNotificationRepository::class
        );
    }

    public function testFindForTransition()
    {
        $this->insertTestNotifications();
        $this->assertCount(1, $this->repository->findForTransition('workflow', 't1', [$this->mc1]));
        $this->assertEquals('n1', $this->repository->findForTransition('workflow', 't1', [$this->mc1])[0]->getName());

        $this->assertCount(1, $this->repository->findForTransition('workflow', 't1', [$this->mc2]));
        $this->assertEquals('n1', $this->repository->findForTransition('workflow', 't1', [$this->mc2])[0]->getName());

        $this->assertCount(1, $this->repository->findForTransition('workflow', 't1', [$this->mc1, $this->mc2]));
        $this->assertEquals(
            'n1',
            $this->repository->findForTransition('workflow', 't1', [$this->mc1, $this->mc2])[0]->getName()
        );

        $this->assertCount(1, $this->repository->findForTransition('workflow', 't2', [$this->mc1]));
        $this->assertEquals('n2', $this->repository->findForTransition('workflow', 't2', [$this->mc1])[0]->getName());

        $this->assertCount(1, $this->repository->findForTransition('workflow', 't2', [$this->mc2]));
        $this->assertEquals('n3', $this->repository->findForTransition('workflow', 't2', [$this->mc2])[0]->getName());

        $this->assertEmpty($this->repository->findForTransition('workflow', 't3', [$this->mc1]));
        $this->assertEmpty($this->repository->findForTransition('workflow', 't3', [$this->mc2]));
    }

    private function insertTestNotifications()
    {
        $this->mc1 = (new MemberCategory())->setName('MC1');
        $this->entityManager->persist($this->mc1);
        $this->mc2 = (new MemberCategory())->setName('MC2');
        $this->entityManager->persist($this->mc2);

        $n1 = (new WorkflowNotification())
            ->addMemberCategory($this->mc1)
            ->addMemberCategory($this->mc2)
            ->setName('n1')
            ->setTemplate('')
            ->setRecipients('')
            ->setSubject('')
            ->setWorkflowName('workflow')
            ->setTransitionName('t1')
            ->setIsEnabled(true);
        $this->entityManager->persist($n1);
        $n2 = (new WorkflowNotification())
            ->addMemberCategory($this->mc1)
            ->setName('n2')
            ->setTemplate('')
            ->setRecipients('')
            ->setSubject('')
            ->setWorkflowName('workflow')
            ->setTransitionName('t2')
            ->setIsEnabled(true);
        $this->entityManager->persist($n2);
        $n3 = (new WorkflowNotification())
            ->addMemberCategory($this->mc2)
            ->setName('n3')
            ->setTemplate('')
            ->setRecipients('')
            ->setSubject('')
            ->setWorkflowName('workflow')
            ->setTransitionName('t2')
            ->setIsEnabled(true);
        $this->entityManager->persist($n3);
        $n4 = (new WorkflowNotification())
            ->addMemberCategory($this->mc1)
            ->setName('n4')
            ->setTemplate('')
            ->setRecipients('')
            ->setSubject('')
            ->setWorkflowName('workflow')
            ->setTransitionName('t3')
            ->setIsEnabled(false);
        $this->entityManager->persist($n4);

        $this->entityManager->flush();
    }
}