<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use App\Repository\ProductImagesRepository;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Product;

#[ORM\Entity(repositoryClass: ProductImagesRepository::class)]
#[ApiResource(
    normalizationContext: [
        'groups' => ['productImages:read'],
    ],

)]
class ProductImages
{
    #[Groups(['product:read','category:read','productImages:read','restoreCart:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'imagePath')]
    private ?Product $product = null;

    #[Groups(['product:read','category:read','productImages:read','restoreCart:read'])]
    #[ORM\ManyToOne(inversedBy: 'productImages')]
    private ?MediaObject $mediaObject = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getMediaObject(): ?MediaObject
    {
        return $this->mediaObject;
    }

    public function setMediaObject(?MediaObject $mediaObject): static
    {
        $this->mediaObject = $mediaObject;

        return $this;
    }

}
