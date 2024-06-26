<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Repository\WorkflowNotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkflowNotificationRepository::class)]
class WorkflowNotification
{
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

    #[ORM\Column(length: 255)]
    private ?string $workflowName = null;

    #[ORM\Column(length: 255)]
    private ?string $transitionName = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column]
    private ?bool $isEnabled = null;

    #[ORM\Column]
    private ?bool $isAllMemberCategories = false;

    #[ORM\Column(nullable: true)]
    private ?bool $replyToPerson = null;


    public function __construct()
    {
        $this->memberCategories = new ArrayCollection();
    }

    //MARK: - Getters/Setters

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

    public function getWorkflowName(): ?string
    {
        return $this->workflowName;
    }

    public function setWorkflowName(string $workflowName): self
    {
        $this->workflowName = $workflowName;

        return $this;
    }

    public function getTransitionName(): ?string
    {
        return $this->transitionName;
    }

    public function setTransitionName(string $transitionName): self
    {
        $this->transitionName = $transitionName;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function isIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function isIsAllMemberCategories(): ?bool
    {
        return $this->isAllMemberCategories;
    }

    public function setIsAllMemberCategories(bool $isAllMemberCategories): self
    {
        $this->isAllMemberCategories = $isAllMemberCategories;

        return $this;
    }

    public function isReplyToPerson(): ?bool
    {
        return $this->replyToPerson;
    }

    public function setReplyToPerson(?bool $replyToPerson): static
    {
        $this->replyToPerson = $replyToPerson;

        return $this;
    }
}
