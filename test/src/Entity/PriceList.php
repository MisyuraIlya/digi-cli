<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PriceListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PriceListRepository::class)]
#[ApiResource]
class PriceList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $extId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?int $discount = null;

    #[ORM\OneToMany(mappedBy: 'priceList', targetEntity: PriceListDetailed::class)]
    private Collection $priceListDetaileds;

    #[ORM\OneToMany(mappedBy: 'priceList', targetEntity: PriceListUser::class)]
    private Collection $priceListUsers;

    public function __construct()
    {
        $this->priceListDetaileds = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->priceListUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExtId(): ?string
    {
        return $this->extId;
    }

    public function setExtId(string $extId): static
    {
        $this->extId = $extId;

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

    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    public function setDiscount(?int $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @return Collection<int, PriceListDetailed>
     */
    public function getPriceListDetaileds(): Collection
    {
        return $this->priceListDetaileds;
    }

    public function addPriceListDetailed(PriceListDetailed $priceListDetailed): static
    {
        if (!$this->priceListDetaileds->contains($priceListDetailed)) {
            $this->priceListDetaileds->add($priceListDetailed);
            $priceListDetailed->setPriceList($this);
        }

        return $this;
    }

    public function removePriceListDetailed(PriceListDetailed $priceListDetailed): static
    {
        if ($this->priceListDetaileds->removeElement($priceListDetailed)) {
            // set the owning side to null (unless already changed)
            if ($priceListDetailed->getPriceList() === $this) {
                $priceListDetailed->setPriceList(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PriceListUser>
     */
    public function getPriceListUsers(): Collection
    {
        return $this->priceListUsers;
    }

    public function addPriceListUser(PriceListUser $priceListUser): static
    {
        if (!$this->priceListUsers->contains($priceListUser)) {
            $this->priceListUsers->add($priceListUser);
            $priceListUser->setPriceList($this);
        }

        return $this;
    }

    public function removePriceListUser(PriceListUser $priceListUser): static
    {
        if ($this->priceListUsers->removeElement($priceListUser)) {
            // set the owning side to null (unless already changed)
            if ($priceListUser->getPriceList() === $this) {
                $priceListUser->setPriceList(null);
            }
        }

        return $this;
    }
}
