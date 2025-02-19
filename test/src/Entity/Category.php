<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\CategoryRepository;
use App\State\AdminCategoriesProvider;
use App\State\CategoriesStateProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\Link;

#[ApiResource(
    normalizationContext: ['groups' => ['category:read']],
    denormalizationContext: ['groups' => ['category:write']],
    order: ['orden' => 'ASC'],
    paginationItemsPerPage: 1000,
)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/adminCategories/{lvl1}/{lvl2}',
            uriVariables: [
                'lvl1' => new Link(fromClass: Category::class),
                'lvl2' => new Link(fromClass: Category::class),
            ],
            provider: AdminCategoriesProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/categoriesApp',
            provider: CategoriesStateProvider::class
        )
    ],
    normalizationContext: ['groups' => ['category:read']],
    denormalizationContext: ['groups' => ['category:write']],
    order: ['orden' => 'ASC']
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'lvlNumber' => 'exact',
    ]
)]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:read'])]
    private ?int $id = null;

    #[Groups(['product:read','category:read'])]
    #[ORM\Column(length: 255)]
    private ?string $extId = null;

    #[Groups(['product:read','category:read','category:write'])]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[Groups(['category:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[Groups(['category:read','category:write'])]
    #[ORM\Column]
    private ?bool $isPublished = null;

    #[Groups(['category:read','category:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $orden = null;

    #[Groups(['category:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $lvlNumber = null;

    #[Groups(['category:read'])]
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'categories')]
    private ?self $parent = null;

    #[Groups(['category:read'])]
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $categories;

    #[ORM\OneToMany(mappedBy: "categoryLvl1", targetEntity: Product::class)]
    private Collection $productsLvl1;

    #[ORM\OneToMany(mappedBy: "categoryLvl2", targetEntity: Product::class)]
    private Collection $productsLvl2;

    #[ORM\OneToMany(mappedBy: "categoryLvl3", targetEntity: Product::class)]
    private Collection $productsLvl3;

    #[Groups(['category:read','category:write'])]
    #[ORM\ManyToOne(inversedBy: 'categories')]
    private ?MediaObject $MediaObject = null;

    #[Groups(['category:read'])]
    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $integrationGroups = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->productsLvl1 = new ArrayCollection();
        $this->productsLvl2 = new ArrayCollection();
        $this->productsLvl3 = new ArrayCollection();
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

    public function setTitle(string $title): static
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

    public function getLvlNumber(): ?int
    {
        return $this->lvlNumber;
    }

    public function setLvlNumber(?int $lvlNumber): static
    {
        $this->lvlNumber = $lvlNumber;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }


    //CUSTOM FUNCTION
    public function setCategories(array $newCategories): static
    {
        $newCategoriesCollection = new ArrayCollection();
        foreach ($newCategories as $newCategory) {
            if ($newCategory instanceof Category) {
                $newCategoriesCollection->add($newCategory);
            }
        }
        $this->categories = $newCategoriesCollection;

        return $this;
    }


    /**
     * @return Collection
     */
    public function getProductsLvl1(): Collection
    {
        return $this->productsLvl1;
    }

    /**
     * @param Collection $productsLvl1
     */
    public function setProductsLvl1(Collection $productsLvl1): void
    {
        $this->productsLvl1 = $productsLvl1;
    }

    /**
     * @return Collection
     */
    public function getProductsLvl2(): Collection
    {
        return $this->productsLvl2;
    }

    /**
     * @param Collection $productsLvl2
     */
    public function setProductsLvl2(Collection $productsLvl2): void
    {
        $this->productsLvl2 = $productsLvl2;
    }

    /**
     * @return Collection
     */
    public function getProductsLvl3(): Collection
    {
        return $this->productsLvl3;
    }

    /**
     * @param Collection $productsLvl3
     */
    public function setProductsLvl3(Collection $productsLvl3): void
    {
        $this->productsLvl3 = $productsLvl3;
    }



    public function addCategory(self $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setparent($this);
        }

        return $this;
    }

    public function removeCategory(self $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getparent() === $this) {
                $category->setparent(null);
            }
        }

        return $this;
    }

    public function getMediaObject(): ?MediaObject
    {
        return $this->MediaObject;
    }

    public function setMediaObject(?MediaObject $MediaObject): static
    {
        $this->MediaObject = $MediaObject;

        return $this;
    }

    public function getIntegrationGroups(): ?array
    {
        return $this->integrationGroups;
    }

    public function setIntegrationGroups(?array $integrationGroups): static
    {
        $this->integrationGroups = $integrationGroups;

        return $this;
    }


}