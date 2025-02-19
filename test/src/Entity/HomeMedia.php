<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\HomeMediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HomeMediaRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['HomeMedia:read']],
    denormalizationContext: ['groups' => ['HomeMedia:write']],
)]
class HomeMedia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['HomeEdit:read','HomeEdit:write','HomeMedia:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'homeMedia')]
    #[Groups(['HomeEdit:read','HomeEdit:write','HomeMedia:read','HomeMedia:write'])]
    private ?MediaObject $media = null;

    #[ORM\ManyToOne(inversedBy: 'homeMedia')]
    #[Groups(['HomeMedia:write'])]
    private ?HomeEdit $home = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedia(): ?MediaObject
    {
        return $this->media;
    }

    public function setMedia(?MediaObject $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function getHome(): ?HomeEdit
    {
        return $this->home;
    }

    public function setHome(?HomeEdit $home): static
    {
        $this->home = $home;

        return $this;
    }
}
