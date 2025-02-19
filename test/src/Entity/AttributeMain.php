<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AttributeMainRepository;
use App\State\AttributeStateProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\Link;
use App\Entity\Category;

#[ORM\Entity(repositoryClass: AttributeMainRepository::class)]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/attribute/{lvl1}/{lvl2}/{lvl3}',
            uriVariables: [
                'lvl1' => new Link(fromClass: Category::class),
                'lvl2' => new Link(fromClass: Category::class),
                'lvl3' => new Link(fromClass: Category::class),
            ],
            provider: AttributeStateProvider::class

        )
    ],

    normalizationContext: ['groups' => ['attribute:read']],
    denormalizationContext: ['groups' => ['attribute:write']],
)]
class AttributeMain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:read','AttributeSub:read','attribute:write','attribute:read','product:read'])]
    private ?int $id = null;

    #[Groups(['category:read','AttributeSub:read','attribute:write','attribute:read','product:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $extId = null;

    #[Groups(['category:read','AttributeSub:read','attribute:write','attribute:read','product:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column]
    private ?bool $isPublished = null;

    #[Groups(['category:read','AttributeSub:read','attribute:write','attribute:read','product:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $orden = null;

    #[Groups(['product:read','category:read','AttributeSub:read','attribute:write','attribute:read'])]
    #[ORM\Column]
    private ?bool $isInProductCard = null;

    #[Groups(['category:read','AttributeSub:read','attribute:write','attribute:read','product:read'])]
    #[ORM\Column]
    private ?bool $isInFilter = null;

    #[Groups(['category:read','AttributeSub:read','attribute:write','attribute:read'])]
    #[ORM\OneToMany(mappedBy: 'attribute', targetEntity: AttributeSub::class)]
    private Collection $SubAttributes;

    public function __construct()
    {
        $this->SubAttributes = new ArrayCollection();
        $this->categories = new ArrayCollection();
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

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

    public function isIsInProductCard(): ?bool
    {
        return $this->isInProductCard;
    }

    public function setIsInProductCard(bool $isInProductCard): static
    {
        $this->isInProductCard = $isInProductCard;

        return $this;
    }

    public function isIsInFilter(): ?bool
    {
        return $this->isInFilter;
    }

    public function setIsInFilter(bool $isInFilter): static
    {
        $this->isInFilter = $isInFilter;

        return $this;
    }

    /**
     * @return Collection<int, AttributeSub>
     */
    public function getSubAttributes(): Collection
    {
        return $this->SubAttributes;
    }

    public function addSubAttribute(AttributeSub $SubAttribute): static
    {
        if (!$this->SubAttributes->contains($SubAttribute)) {
            $this->SubAttributes->add($SubAttribute);
            $SubAttribute->setAttributeId($this);
        }

        return $this;
    }

    public function removeSubAttribute(AttributeSub $SubAttribute): static
    {
        if ($this->SubAttributes->removeElement($SubAttribute)) {
            // set the owning side to null (unless already changed)
            if ($SubAttribute->getAttributeId() === $this) {
                $SubAttribute->setAttributeId(null);
            }
        }

        return $this;
    }

}
