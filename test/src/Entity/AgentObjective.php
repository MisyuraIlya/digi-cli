<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Enum\AgentObjectiveType;
use App\Repository\AgentObjectiveRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ApiResource(
    normalizationContext: [
        'groups' => ['agentObjective:read'],
        'enable_max_depth' => true,
    ],
    paginationClientItemsPerPage: true,
)]

#[ORM\Entity(repositoryClass: AgentObjectiveRepository::class)]

#[ApiFilter(
    SearchFilter::class,
    properties: [
        'agent.id' => 'exact',
        'objectiveType' => 'exact',
        'client.extId' => 'partial',
        'client.name' => 'partial'
    ]
)]

#[ApiFilter(DateFilter::class, properties: ['date'])]
class AgentObjective
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['agentObjective:read'])]
    private ?int $id = null;
    #[Groups(['agentObjective:read'])]
    #[ORM\ManyToOne(inversedBy: 'agentObjectives')]
    private ?User $agent = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\ManyToOne(inversedBy: 'clientObjectives')]
    private ?User $client = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column]
    private ?bool $isCompleted = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column]
    private ?bool $week1 = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column]
    private ?bool $week2 = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column]
    private ?bool $week3 = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column]
    private ?bool $week4 = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column(nullable: true)]
    private ?string $hourFrom = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column(nullable: true)]
    private ?string  $hourTo = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $choosedDay = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Groups(['agentObjective:read'])]
    #[ORM\Column(length: 255)]
    private ?AgentObjectiveType $objectiveType = null;

    #[Groups(['agentObjective:read'])]
    #[SerializedName("subTusk")]
    private Collection $subTusk;

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

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;

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

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isWeek1(): ?bool
    {
        return $this->week1;
    }

    public function setWeek1(bool $week1): static
    {
        $this->week1 = $week1;

        return $this;
    }

    public function isWeek2(): ?bool
    {
        return $this->week2;
    }

    public function setWeek2(bool $week2): static
    {
        $this->week2 = $week2;

        return $this;
    }

    public function isWeek3(): ?bool
    {
        return $this->week3;
    }

    public function setWeek3(bool $week3): static
    {
        $this->week3 = $week3;

        return $this;
    }

    public function isWeek4(): ?bool
    {
        return $this->week4;
    }

    public function setWeek4(bool $week4): static
    {
        $this->week4 = $week4;

        return $this;
    }

    public function getHourFrom(): ?string
    {
        return $this->hourFrom;
    }

    public function setHourFrom(?string  $hourFrom): static
    {
        $this->hourFrom = $hourFrom;

        return $this;
    }


    public function getHourTo(): ?string
    {
        return $this->hourTo;
    }

    public function setHourTo(?string  $hourTo): static
    {
        $this->hourTo = $hourTo;

        return $this;
    }

    public function getChoosedDay(): ?string
    {
        return $this->choosedDay;
    }

    public function setChoosedDay(?string $choosedDay): static
    {
        $this->choosedDay = $choosedDay;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getObjectiveType(): ?AgentObjectiveType
    {
        return $this->objectiveType;
    }

    public function setObjectiveType(AgentObjectiveType $objectiveType): static
    {
        $this->objectiveType = $objectiveType;

        return $this;
    }

//    /**
//     * @return Collection<int, self>
//     */
    public function getSubTusk()
    {
        $res = $this->subTusk->toArray();
        $newRes = [];
        foreach ($res as $itemRec){
            assert($itemRec instanceof AgentObjective);
            $newObj = new \stdClass();
            $newObj->id = $itemRec->getId();
            $newObj->agent = $itemRec->getAgent();
            $newObj->client = $itemRec->getClient();
            $newObj->isCompleted = $itemRec->isIsCompleted();
            $newObj->completedAt = $itemRec->getCompletedAt();
            $newObj->title = $itemRec->getTitle();
            $newObj->description = $itemRec->getDescription();
            $newObj->week1 = $itemRec->isWeek1();
            $newObj->week2 = $itemRec->isWeek2();
            $newObj->week3 = $itemRec->isWeek3();
            $newObj->week4 = $itemRec->isWeek4();
            $newObj->hourFrom = $itemRec->getHourFrom();
            $newObj->hourTo = $itemRec->getHourTo();
            $newObj->createdAt = $itemRec->getCreatedAt();
            $newObj->updatedAt = $itemRec->getUpdatedAt();
            $newObj->objectiveType = $itemRec->getObjectiveType();
            $newRes[] = $newObj;
        }

        return $newRes;
    }


    //CUSTOM FUNCTION
    public function setSubStuck(array $subTucks): static
    {
        $newSubStuckCollection = new ArrayCollection();
        foreach ($subTucks as $newSubStuck) {
            if ($newSubStuck instanceof AgentObjective) {
                $newSubStuckCollection->add($newSubStuck);
            }
        }
        $this->subTusk = $newSubStuckCollection;

        return $this;
    }
}
