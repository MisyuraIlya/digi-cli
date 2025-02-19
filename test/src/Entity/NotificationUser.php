<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\NotificationUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationUserRepository::class)]
#[ApiResource(
    normalizationContext: [
        'groups' => ['notificationUser:read'],
    ],
    paginationClientItemsPerPage: true,
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'user.id' => 'exact',
    ]
)]
#[ApiFilter(BooleanFilter::class, properties: ['isRead'])]

class NotificationUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['notificationUser:read'])]
    private ?int $id = null;

    #[Groups(['notificationUser:read'])]
    #[ORM\ManyToOne(inversedBy: 'notificationUsers')]
    private ?User $user = null;

    #[Groups(['notificationUser:read'])]
    #[ORM\ManyToOne(inversedBy: 'notificationUsers')]
    private ?Notification $notification = null;

    #[Groups(['notificationUser:read'])]
    #[ORM\Column]
    private ?bool $isRead = null;
    #[Groups(['notificationUser:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getNotification(): ?Notification
    {
        return $this->notification;
    }

    public function setNotification(?Notification $notification): static
    {
        $this->notification = $notification;

        return $this;
    }

    public function isIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;

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
}
