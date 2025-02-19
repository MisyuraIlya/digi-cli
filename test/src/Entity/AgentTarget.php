<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\AgentTargetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: [
        'groups' => ['agentTarget:read'],
    ],
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'agent.id' => 'exact',
        'year' => 'exact'
    ]
)]
#[ORM\Entity(repositoryClass: AgentTargetRepository::class)]
class AgentTarget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['agentTarget:read'])]
    private ?int $id = null;

    #[Groups(['agentTarget:read'])]
    #[ORM\ManyToOne(inversedBy: 'agentTargets')]
    private ?User $agent = null;

    #[Groups(['agentTarget:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $month = null;

    #[Groups(['agentTarget:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $year = null;

    #[Groups(['agentTarget:read'])]
    #[ORM\Column]
    private ?int $currentValue = null;

    #[Groups(['agentTarget:read'])]
    #[ORM\Column]
    private ?int $targetValue = null;

    #[Groups(['agentTarget:read'])]
    #[ORM\Column]
    private ?bool $isCompleted = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgent(): ?User
    {
        return $this->agent;
    }

    public function setAgent(?User $agent): static
    {
        $this->agent = $agent;

        return $this;
    }

    public function getMonth(): ?string
    {
        return $this->month;
    }

    public function setMonth(?string $month): static
    {
        $this->month = $month;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(?string $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getCurrentValue(): ?int
    {
        return $this->currentValue;
    }

    public function setCurrentValue(int $currentValue): static
    {
        $this->currentValue = $currentValue;

        return $this;
    }

    public function getTargetValue(): ?int
    {
        return $this->targetValue;
    }

    public function setTargetValue(int $targetValue): static
    {
        $this->targetValue = $targetValue;

        return $this;
    }

    public function isIsCompleted(): ?bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(bool $isCompleted): static
    {
        $this->isCompleted = $isCompleted;

        return $this;
    }
}
