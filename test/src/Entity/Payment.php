<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PaymentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ApiResource]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    private ?User $user = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $json = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $token = null;

    #[ORM\Column(nullable: true)]
    private ?float $amount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $yaadAcCode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $jsonJ5 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $j5Id = null;

    #[ORM\OneToMany(mappedBy: 'payment', targetEntity: History::class)]
    private Collection $histories;

    public function __construct()
    {
        $this->histories = new ArrayCollection();
    }

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

    public function getJson(): ?string
    {
        return $this->json;
    }

    public function setJson(?string $json): static
    {
        $this->json = $json;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getYaadAcCode(): ?string
    {
        return $this->yaadAcCode;
    }

    public function setYaadAcCode(?string $yaadAcCode): static
    {
        $this->yaadAcCode = $yaadAcCode;

        return $this;
    }

    public function getJsonJ5(): ?string
    {
        return $this->jsonJ5;
    }

    public function setJsonJ5(?string $jsonJ5): static
    {
        $this->jsonJ5 = $jsonJ5;

        return $this;
    }

    public function getJ5Id(): ?string
    {
        return $this->j5Id;
    }

    public function setJ5Id(?string $j5Id): static
    {
        $this->j5Id = $j5Id;

        return $this;
    }

    /**
     * @return Collection<int, History>
     */
    public function getHistories(): Collection
    {
        return $this->histories;
    }

    public function addHistory(History $history): static
    {
        if (!$this->histories->contains($history)) {
            $this->histories->add($history);
            $history->setPayment($this);
        }

        return $this;
    }

    public function removeHistory(History $history): static
    {
        if ($this->histories->removeElement($history)) {
            // set the owning side to null (unless already changed)
            if ($history->getPayment() === $this) {
                $history->setPayment(null);
            }
        }

        return $this;
    }
}
