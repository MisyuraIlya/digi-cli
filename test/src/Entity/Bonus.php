<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\BonusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BonusRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: [
                'groups' => ['product:read','bonus:details'],
            ],
        ),
        new GetCollection(
            paginationEnabled: false,
        ),
        new Post(),
        new Put(),
        new Patch(),
    ],
)]
class Bonus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userExtId = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['bonus:read', 'bonus:details'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['bonus:read', 'bonus:details'])]
    private ?\DateTimeImmutable $expiredAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['bonus:read', 'bonus:details'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['bonus:read', 'bonus:details'])]
    private ?string $extId = null;

    #[ORM\OneToMany(mappedBy: 'bonus', targetEntity: BonusDetailed::class)]
    #[Groups(['bonus:read', 'bonus:details'])]
    private Collection $bonusDetaileds;

    public function __construct()
    {
        $this->bonusDetaileds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserExtId(): ?string
    {
        return $this->userExtId;
    }

    public function setUserExtId(?string $userExtId): static
    {
        $this->userExtId = $userExtId;

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

    public function getExpiredAt(): ?\DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(\DateTimeImmutable $expiredAt): static
    {
        $this->expiredAt = $expiredAt;

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

    public function getExtId(): ?string
    {
        return $this->extId;
    }

    public function setExtId(?string $extId): static
    {
        $this->extId = $extId;

        return $this;
    }

    /**
     * @return Collection<int, BonusDetailed>
     */
    public function getBonusDetaileds(): Collection
    {
        return $this->bonusDetaileds;
    }

    public function addBonusDetailed(BonusDetailed $bonusDetailed): static
    {
        if (!$this->bonusDetaileds->contains($bonusDetailed)) {
            $this->bonusDetaileds->add($bonusDetailed);
            $bonusDetailed->setBonus($this);
        }

        return $this;
    }

    public function removeBonusDetailed(BonusDetailed $bonusDetailed): static
    {
        if ($this->bonusDetaileds->removeElement($bonusDetailed)) {
            // set the owning side to null (unless already changed)
            if ($bonusDetailed->getBonus() === $this) {
                $bonusDetailed->setBonus(null);
            }
        }

        return $this;
    }
}
