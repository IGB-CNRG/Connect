<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity\Workflow;

use App\Entity\MemberCategory;
use App\Repository\WorkflowNotificationRepository;
use App\Workflow\Enum\WorkflowEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkflowNotificationRepository::class)]
class WorkflowNotification
{
    use StageRelationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $template = null;

    #[ORM\Column(length: 255)]
    private ?string $recipients = null;

    #[ORM\ManyToMany(targetEntity: MemberCategory::class)]
    private Collection $memberCategories;

    #[ORM\Column(enumType: WorkflowEvent::class)]
    private ?WorkflowEvent $event = null;


    public function __construct()
    {
        $this->memberCategories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getRecipients(): ?string
    {
        return $this->recipients;
    }

    public function setRecipients(string $recipients): self
    {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * @return Collection<int, MemberCategory>
     */
    public function getMemberCategories(): Collection
    {
        return $this->memberCategories;
    }

    public function addMemberCategory(MemberCategory $memberCategory): self
    {
        if (!$this->memberCategories->contains($memberCategory)) {
            $this->memberCategories->add($memberCategory);
        }

        return $this;
    }

    public function removeMemberCategory(MemberCategory $memberCategory): self
    {
        $this->memberCategories->removeElement($memberCategory);

        return $this;
    }

    public function getEvent(): ?WorkflowEvent
    {
        return $this->event;
    }

    public function setEvent(WorkflowEvent $event): self
    {
        $this->event = $event;

        return $this;
    }
}
