<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AttributeSubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Product;
use App\Entity\AttributeMain;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\Link;

#[ORM\Entity(repositoryClass: AttributeSubRepository::class)]
#[ApiResource]
#[ApiResource(
    normalizationContext: ['groups' => ['AttributeSub:read']],
    denormalizationContext: ['groups' => ['AttributeSub:write']],
)]
class AttributeSub
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read','AttributeSub:read', 'attribute:read'])]
    private ?int $id = null;

    #[Groups(['product:read','AttributeSub:read', 'attribute:read'])]
    #[ORM\ManyToOne(inversedBy: 'SubAttributes')]
    private ?AttributeMain $attribute = null;

    #[Groups(['product:read','AttributeSub:read', 'attribute:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\OneToMany(mappedBy: 'attributeSub', targetEntity: ProductAttribute::class)]
    private Collection $productAttributes;

    #[Groups(['product:read','AttributeSub:read', 'attribute:read'])]
    public ?int $productCount = null;
    public function __construct()
    {
        $this->productAttributes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttribute(): ?AttributeMain
    {
        return $this->attribute;
    }

    public function setAttribute(?AttributeMain $attribute): static
    {
        $this->attribute = $attribute;

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

    /**
     * @return Collection<int, ProductAttribute>
     */
    public function getProductAttributes(): Collection
    {
        return $this->productAttributes;
    }

    public function addProductAttribute(ProductAttribute $productAttribute): static
    {
        if (!$this->productAttributes->contains($productAttribute)) {
            $this->productAttributes->add($productAttribute);
            $productAttribute->setAttributeSub($this);
        }

        return $this;
    }

    public function removeProductAttribute(ProductAttribute $productAttribute): static
    {
        if ($this->productAttributes->removeElement($productAttribute)) {
            // set the owning side to null (unless already changed)
            if ($productAttribute->getAttributeSub() === $this) {
                $productAttribute->setAttributeSub(null);
            }
        }

        return $this;
    }

    /**
     * Get the count of products associated with this AttributeSub
     */
    public function getProductCount(): ?int
    {
        return $this->productCount;
    }

    /**
     * Set the count of products associated with this AttributeSub
     */
    public function setProductCount(int $count): self
    {
        $this->productCount = $count;

        return $this;
    }
}
