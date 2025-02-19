<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Enum\DocumentsType;
use App\Enum\PurchaseStatus;
use App\Repository\HistoryRepository;
use App\State\HistoryStateProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HistoryRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['history:read']],
//    provider: HistoryStateProvider::class,
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'user.extId' => 'partial',
    ]
)]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
class History
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['history:read'])]
    private ?int $id = null;

    #[Groups(['history:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $orderExtId = null;

    #[Groups(['history:read','history:read'])]
    #[ORM\ManyToOne(inversedBy: 'histories')]
    private ?User $user = null;

    #[Groups(['history:read'])]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deliveryDate = null;

    #[Groups(['history:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $discount = null;

    #[Groups(['history:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $total = null;

    #[Groups(['history:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $orderComment = null;

    #[Groups(['history:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?PurchaseStatus $orderStatus = null;

    #[Groups(['history:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['history:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Groups(['history:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $deliveryPrice = null;

    #[Groups(['history:read','historyDetailed:read'])]
    #[ORM\OneToMany(mappedBy: 'history', targetEntity: HistoryDetailed::class)]
    private Collection $historyDetaileds;

    #[Groups(['history:read'])]
    #[ORM\Column(length: 255)]
    private ?DocumentsType $documentType = null;

    #[Groups(['history:read','historyDetailed:read'])]
    #[ORM\ManyToOne(inversedBy: 'histories')]
    private ?User $agent = null;

    #[Groups(['history:read','historyDetailed:read'])]
    #[ORM\ManyToOne(inversedBy: 'histories')]
    private ?User $agentApproved = null;

    #[Groups(['history:read','historyDetailed:read'])]
    #[ORM\Column]
    private ?bool $isBuyByCreditCard = null;

    #[Groups(['history:read','historyDetailed:read'])]
    #[ORM\Column]
    private ?bool $isSendErp = null;

    #[Groups(['history:read','historyDetailed:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $sendErpAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $json = null;

    #[Groups(['history:read','historyDetailed:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $error = null;

    #[ORM\ManyToOne(inversedBy: 'histories')]
    private ?Payment $payment = null;

    #[ORM\Column]
    private ?float $tax = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryType = null;

    public function __construct()
    {
        $this->historyDetaileds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderExtId(): ?string
    {
        return $this->orderExtId;
    }

    public function setOrderExtId(?string $orderExtId): static
    {
        $this->orderExtId = $orderExtId;

        return $this;
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

    public function getDeliveryDate(): ?\DateTimeInterface
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(?\DateTimeInterface $deliveryDate): static
    {
        $this->deliveryDate = $deliveryDate;

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

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getOrderComment(): ?string
    {
        return $this->orderComment;
    }

    public function setOrderComment(?string $orderComment): static
    {
        $this->orderComment = $orderComment;

        return $this;
    }

    public function getOrderStatus(): ?PurchaseStatus
    {
        return $this->orderStatus;
    }

    public function setOrderStatus(?PurchaseStatus $orderStatus): static
    {
        $this->orderStatus = $orderStatus;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeliveryPrice(): ?int
    {
        return $this->deliveryPrice;
    }

    public function setDeliveryPrice(?int $deliveryPrice): static
    {
        $this->deliveryPrice = $deliveryPrice;

        return $this;
    }

    /**
     * @return Collection<int, HistoryDetailed>
     */
    public function getHistoryDetaileds(): Collection
    {
        return $this->historyDetaileds;
    }

    public function addHistoryDetailed(HistoryDetailed $historyDetailed): static
    {
        if (!$this->historyDetaileds->contains($historyDetailed)) {
            $this->historyDetaileds->add($historyDetailed);
            $historyDetailed->setHistory($this);
        }

        return $this;
    }

    public function removeHistoryDetailed(HistoryDetailed $historyDetailed): static
    {
        if ($this->historyDetaileds->removeElement($historyDetailed)) {
            // set the owning side to null (unless already changed)
            if ($historyDetailed->getHistory() === $this) {
                $historyDetailed->setHistory(null);
            }
        }

        return $this;
    }

    public function getDocumentType(): ?DocumentsType
    {
        return $this->documentType;
    }

    public function setDocumentType(?DocumentsType $documentType): static
    {
        $this->documentType = $documentType;

        return $this;
    }

    public function getAgent(): ?User
    {
        return $this->agent;
    }

    public function setAgent(?User $agent): static
    {
        $this->agent = $agent;

        return $this;
    }

    public function getAgentApproved(): ?User
    {
        return $this->agentApproved;
    }

    public function setAgentApproved(?User $agent): static
    {
        $this->agentApproved = $agent;

        return $this;
    }

    public function isIsBuyByCreditCard(): ?bool
    {
        return $this->isBuyByCreditCard;
    }

    public function setIsBuyByCreditCard(bool $isBuyByCreditCard): static
    {
        $this->isBuyByCreditCard = $isBuyByCreditCard;

        return $this;
    }

    public function isIsSendErp(): ?bool
    {
        return $this->isSendErp;
    }

    public function setIsSendErp(bool $isSendErp): static
    {
        $this->isSendErp = $isSendErp;

        return $this;
    }

    public function getSendErpAt(): ?\DateTimeImmutable
    {
        return $this->sendErpAt;
    }

    public function setSendErpAt(?\DateTimeImmutable $sendErpAt): static
    {
        $this->sendErpAt = $sendErpAt;

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

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): static
    {
        $this->error = $error;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): static
    {
        $this->payment = $payment;

        return $this;
    }

    public function getTax(): ?float
    {
        return $this->tax;
    }

    public function setTax(float $tax): static
    {
        $this->tax = $tax;

        return $this;
    }

    public function getDeliveryType(): ?string
    {
        return $this->deliveryType;
    }

    public function setDeliveryType(?string $deliveryType): static
    {
        $this->deliveryType = $deliveryType;

        return $this;
    }
}
