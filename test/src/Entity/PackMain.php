<?php

namespace App\Entity;

use App\Repository\PackMainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PackMainRepository::class)]
class PackMain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read','category:read','restoreCart:read','history:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['product:read','category:read','restoreCart:read','history:read'])]
    private ?string $extId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['product:read','category:read','restoreCart:read','history:read'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['product:read','category:read','restoreCart:read','history:read'])]
    private ?int $quantity = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['product:read','category:read','restoreCart:read','history:read'])]
    private ?string $barcode = null;

    #[ORM\OneToMany(mappedBy: 'pack', targetEntity: ProductPack::class)]
    private Collection $packProducts;

    public function __construct()
    {
        $this->packProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(?string $barcode): static
    {
        $this->barcode = $barcode;

        return $this;
    }

    /**
     * @return Collection<int, ProductPack>
     */
    public function getPackProducts(): Collection
    {
        return $this->packProducts;
    }

    public function addPackProduct(ProductPack $packProduct): static
    {
        if (!$this->packProducts->contains($packProduct)) {
            $this->packProducts->add($packProduct);
            $packProduct->setPack($this);
        }

        return $this;
    }

    public function removePackProduct(ProductPack $packProduct): static
    {
        if ($this->packProducts->removeElement($packProduct)) {
            // set the owning side to null (unless already changed)
            if ($packProduct->getPack() === $this) {
                $packProduct->setPack(null);
            }
        }

        return $this;
    }
}
