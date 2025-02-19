<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\HomeEditRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HomeEditRepository::class)]

#[ApiResource(
    normalizationContext: ['groups' => ['HomeEdit:read']],
    denormalizationContext: ['groups' => ['HomeEdit:write']],
    order: ['orden' => 'ASC'],
)]

class HomeEdit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['HomeEdit:read'])]
    private ?int $id = null;

    #[Groups(['HomeEdit:read','HomeEdit:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['HomeEdit:read','HomeEdit:write'])]
    private ?int $orden = null;

    #[ORM\Column]
    #[Groups(['HomeEdit:read','HomeEdit:write'])]
    private ?bool $isVideo = null;

    #[ORM\Column]
    #[Groups(['HomeEdit:read','HomeEdit:write'])]
    private ?bool $isBanner = null;

    #[ORM\Column]
    #[Groups(['HomeEdit:read','HomeEdit:write'])]
    private ?bool $isActive = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['HomeEdit:read','HomeEdit:write'])]
    private ?int $count = null;

    #[ORM\OneToMany(mappedBy: 'home', targetEntity: HomeMedia::class)]
    #[Groups(['HomeEdit:read','HomeEdit:write'])]
    private Collection $homeMedia;

    #[ORM\Column(nullable: true)]
    #[Groups(['HomeEdit:read','HomeEdit:write'])]
    private ?int $countMobile = null;

    #[ORM\Column]
    #[Groups(['HomeEdit:read','HomeEdit:write'])]
    private ?bool $isPopUp = null;

    #[ORM\Column]
    #[Groups(['HomeEdit:read','HomeEdit:write'])]
    private ?bool $isDeletable = null;

    public function __construct()
    {
        $this->homeMedia = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(?int $orden): static
    {
        $this->orden = $orden;

        return $this;
    }

    public function isIsVideo(): ?bool
    {
        return $this->isVideo;
    }

    public function setIsVideo(bool $isVideo): static
    {
        $this->isVideo = $isVideo;

        return $this;
    }

    public function isIsBanner(): ?bool
    {
        return $this->isBanner;
    }

    public function setIsBanner(bool $isBanner): static
    {
        $this->isBanner = $isBanner;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(?int $count): static
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return Collection<int, HomeMedia>
     */
    public function getHomeMedia(): Collection
    {
        return $this->homeMedia;
    }

    public function addHomeMedium(HomeMedia $homeMedium): static
    {
        if (!$this->homeMedia->contains($homeMedium)) {
            $this->homeMedia->add($homeMedium);
            $homeMedium->setHome($this);
        }

        return $this;
    }

    public function removeHomeMedium(HomeMedia $homeMedium): static
    {
        if ($this->homeMedia->removeElement($homeMedium)) {
            // set the owning side to null (unless already changed)
            if ($homeMedium->getHome() === $this) {
                $homeMedium->setHome(null);
            }
        }

        return $this;
    }

    public function getCountMobile(): ?int
    {
        return $this->countMobile;
    }

    public function setCountMobile(?int $countMobile): static
    {
        $this->countMobile = $countMobile;

        return $this;
    }

    public function isIsPopUp(): ?bool
    {
        return $this->isPopUp;
    }

    public function setIsPopUp(bool $isPopUp): static
    {
        $this->isPopUp = $isPopUp;

        return $this;
    }

    public function isIsDeletable(): ?bool
    {
        return $this->isDeletable;
    }

    public function setIsDeletable(bool $isDeletable): static
    {
        $this->isDeletable = $isDeletable;

        return $this;
    }
}
