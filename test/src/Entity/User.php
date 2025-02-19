<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Enum\CatalogDocumentTypeEnum;
use App\Enum\UsersTypes;
use App\Repository\UserRepository;
use App\State\AgentClientsProvider;
use App\State\ProductProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;

#[ORM\Entity(repositoryClass: UserRepository::class)]

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/agentClients/{agentId}',
            uriVariables: [
                'agentId' => new Link(fromClass: User::class),
            ],
            paginationClientItemsPerPage: true,
            normalizationContext: [
                'groups' => ['userAgent:read'],
            ],
            denormalizationContext: [
                'groups' => ['userAgent:write'],
            ],
            provider: AgentClientsProvider::class,
        )
    ],
)]

#[ApiResource(
    normalizationContext: [
        'groups' => ['user:read'],
    ],
    paginationClientItemsPerPage: true,
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'id' => 'exact',
        'name' => 'partial',
        'extId' => 'partial',
        'role' => 'exact',
        'search' => 'partial'
    ]
)]

#[ApiFilter(BooleanFilter::class, properties: ['isAgent','isBlocked','isRegistered'])]


class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true, nullable: true)]
    #[Groups(['user:read','agentTarget:read','userAgent:read','notificationUser:read'])]
    private ?string $email = null;

    #[Groups(['user:read','agentTarget:read','notificationUser:read'])]
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column]
    #[Groups(['user:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    private ?bool $isRegistered = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read','history:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read','history:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    private ?string $extId = null;

    #[ORM\Column]
    #[Groups(['user:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    private ?bool $isBlocked = null;

    #[ORM\Column]
    #[Groups(['user:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['user:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $recovery = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: History::class)]
    private Collection $histories;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Migvan::class)]
    private Collection $migvans;

    #[ORM\OneToMany(mappedBy: 'agent', targetEntity: AgentTarget::class)]
    private Collection $agentTargets;

    #[ORM\OneToMany(mappedBy: 'agent', targetEntity: AgentObjective::class)]
    private Collection $agentObjectives;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: AgentObjective::class)]
    private Collection $clientObjectives;

    #[Groups(['user:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?UsersTypes $role = null;

    #[Groups(['user:read','agentTarget:read','agentObjective:read','notificationUser:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $passwordUnencrypted = null;

    #[Groups(['user:read','agentTarget:read','agentObjective:read','notificationUser:read'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false, "comment" => "for agent only (agent can send order without approve)"])]
    private ?bool $isAllowOrder = false;


    #[Groups(['user:read', 'agentTarget:read', 'agentObjective:read','notificationUser:read'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false, "comment" => "for agent only (can all clients not only yours)"])]
    private ?bool $isAllowAllClients = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PriceListUser::class)]
    private Collection $priceListUsers;

    #[Groups(['user:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $payCode = null;

    #[Groups(['user:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $PayDes = null;

    #[Groups(['user:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $maxCredit = null;

    #[Groups(['user:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $maxObligo = null;

    #[Groups(['user:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hp = null;

    #[Groups(['user:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $taxCode = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'users')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $users;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'usersAgent')]
    private ?self $agent = null;

    #[ORM\OneToMany(mappedBy: 'agent', targetEntity: self::class)]
    private Collection $usersAgent;

    #[Groups(['user:read','agentTarget:read','notificationUser:read'])]
    #[ORM\Column]
    private ?bool $isAgent = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: NotificationUser::class)]
    private Collection $notificationUsers;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $search = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $oneSignalAppId = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Payment::class)]
    private Collection $payments;

    #[Groups(['user:read','history:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[Groups(['user:read','history:read','agentTarget:read','agentObjective:read','userAgent:read','notificationUser:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column]
    private ?bool $isVatEnabled = null;



    public function __construct()
    {
        $this->histories = new ArrayCollection();
        $this->migvans = new ArrayCollection();
        $this->agentTargets = new ArrayCollection();
        $this->agentObjectives = new ArrayCollection();
        $this->clientObjectives = new ArrayCollection();
        $this->priceListUsers = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->usersAgent = new ArrayCollection();
        $this->notificationUsers = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
//        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(UsersTypes $roles): static
    {
        $this->roles = $roles->getRoles();

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getIsRegistered(): ?bool
    {
        return $this->isRegistered;
    }

    public function setIsRegistered(bool $isRegistered): static
    {
        $this->isRegistered = $isRegistered;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
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

    public function getIsBlocked(): ?bool
    {
        return $this->isBlocked;
    }

    public function setIsBlocked(bool $isBlocked): static
    {
        $this->isBlocked = $isBlocked;

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

    public function getRecovery(): ?string
    {
        return $this->recovery;
    }

    public function setRecovery(?string $recovery): static
    {
        $this->recovery = $recovery;

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
            $history->setUser($this);
        }

        return $this;
    }

    public function removeHistory(History $history): static
    {
        if ($this->histories->removeElement($history)) {
            // set the owning side to null (unless already changed)
            if ($history->getUser() === $this) {
                $history->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Migvan>
     */
    public function getMigvans(): Collection
    {
        return $this->migvans;
    }

    public function addMigvan(Migvan $migvan): static
    {
        if (!$this->migvans->contains($migvan)) {
            $this->migvans->add($migvan);
            $migvan->setUser($this);
        }

        return $this;
    }

    public function removeMigvan(Migvan $migvan): static
    {
        if ($this->migvans->removeElement($migvan)) {
            // set the owning side to null (unless already changed)
            if ($migvan->getUser() === $this) {
                $migvan->setUser(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, AgentTarget>
     */
    public function getAgentTargets(): Collection
    {
        return $this->agentTargets;
    }

    public function addAgentTarget(AgentTarget $agentTarget): static
    {
        if (!$this->agentTargets->contains($agentTarget)) {
            $this->agentTargets->add($agentTarget);
            $agentTarget->setAgent($this);
        }

        return $this;
    }

    public function removeAgentTarget(AgentTarget $agentTarget): static
    {
        if ($this->agentTargets->removeElement($agentTarget)) {
            // set the owning side to null (unless already changed)
            if ($agentTarget->getAgent() === $this) {
                $agentTarget->setAgent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AgentObjective>
     */
    public function getAgentObjectives(): Collection
    {
        return $this->agentObjectives;
    }

    public function addAgentObjective(AgentObjective $agentObjective): static
    {
        if (!$this->agentObjectives->contains($agentObjective)) {
            $this->agentObjectives->add($agentObjective);
            $agentObjective->setAgent($this);
        }

        return $this;
    }

    public function removeAgentObjective(AgentObjective $agentObjective): static
    {
        if ($this->agentObjectives->removeElement($agentObjective)) {
            // set the owning side to null (unless already changed)
            if ($agentObjective->getAgent() === $this) {
                $agentObjective->setAgent(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, AgentObjective>
     */
    public function getClientObjectives(): Collection
    {
        return $this->clientObjectives;
    }

    public function addClientObjective(AgentObjective $clientObjective): static
    {
        if (!$this->clientObjectives->contains($clientObjective)) {
            $this->clientObjectives->add($clientObjective);
            $clientObjective->setClient($this);
        }

        return $this;
    }

    public function removeClientObjective(AgentObjective $clientObjective): static
    {
        if ($this->agentObjectives->removeElement($clientObjective)) {
            if ($clientObjective->getClient() === $this) {
                $clientObjective->setClient(null);
            }
        }

        return $this;
    }

    public function getRole(): ?UsersTypes
    {
        return $this->role;
    }

    public function setRole(UsersTypes $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getPasswordUnencrypted(): ?string
    {
        return $this->passwordUnencrypted;
    }

    public function setPasswordUnencrypted(?string $passwordUnencrypted): static
    {
        $this->passwordUnencrypted = $passwordUnencrypted;

        return $this;
    }

    public function isIsAllowOrder(): ?bool
    {
        return $this->isAllowOrder;
    }

    public function setIsAllowOrder(bool $isAllowOrder): static
    {
        $this->isAllowOrder = $isAllowOrder;

        return $this;
    }

    public function isIsAllowAllClients(): ?bool
    {
        return $this->isAllowAllClients;
    }

    public function setIsAllowAllClients(bool $isAllowAllClients): static
    {
        $this->isAllowAllClients = $isAllowAllClients;

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
            $priceListUser->setUser($this);
        }

        return $this;
    }

    public function removePriceListUser(PriceListUser $priceListUser): static
    {
        if ($this->priceListUsers->removeElement($priceListUser)) {
            // set the owning side to null (unless already changed)
            if ($priceListUser->getUser() === $this) {
                $priceListUser->setUser(null);
            }
        }

        return $this;
    }

    public function getPayCode(): ?string
    {
        return $this->payCode;
    }

    public function setPayCode(?string $payCode): static
    {
        $this->payCode = $payCode;

        return $this;
    }

    public function getPayDes(): ?string
    {
        return $this->PayDes;
    }

    public function setPayDes(?string $PayDes): static
    {
        $this->PayDes = $PayDes;

        return $this;
    }

    public function getMaxCredit(): ?float
    {
        return $this->maxCredit;
    }

    public function setMaxCredit(?float $maxCredit): static
    {
        $this->maxCredit = $maxCredit;

        return $this;
    }

    public function getMaxObligo(): ?float
    {
        return $this->maxObligo;
    }

    public function setMaxObligo(?float $maxObligo): static
    {
        $this->maxObligo = $maxObligo;

        return $this;
    }

    public function getHp(): ?string
    {
        return $this->hp;
    }

    public function setHp(?string $hp): static
    {
        $this->hp = $hp;

        return $this;
    }

    public function getTaxCode(): ?string
    {
        return $this->taxCode;
    }

    public function setTaxCode(?string $taxCode): static
    {
        $this->taxCode = $taxCode;

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
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(self $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setParent($this);
        }

        return $this;
    }

    public function removeUser(self $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getParent() === $this) {
                $user->setParent(null);
            }
        }

        return $this;
    }

    public function isIsAgent(): ?bool
    {
        return $this->isAgent;
    }

    public function setIsAgent(bool $isAgent): static
    {
        $this->isAgent = $isAgent;

        return $this;
    }

    public function getAgentId(): ?self
    {
        return $this->agent;
    }

    public function setAgentId(?self $agent): static
    {
        $this->agent = $agent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getUsersAgent(): Collection
    {
        return $this->usersAgent;
    }

    public function addUsersAgent(self $usersAgent): static
    {
        if (!$this->usersAgent->contains($usersAgent)) {
            $this->usersAgent->add($usersAgent);
            $usersAgent->setAgentId($this);
        }

        return $this;
    }

    public function removeUsersAgent(self $usersAgent): static
    {
        if ($this->usersAgent->removeElement($usersAgent)) {
            // set the owning side to null (unless already changed)
            if ($usersAgent->getAgentId() === $this) {
                $usersAgent->setAgentId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, NotificationUser>
     */
    public function getNotificationUsers(): Collection
    {
        return $this->notificationUsers;
    }

    public function addNotificationUser(NotificationUser $notificationUser): static
    {
        if (!$this->notificationUsers->contains($notificationUser)) {
            $this->notificationUsers->add($notificationUser);
            $notificationUser->setUser($this);
        }

        return $this;
    }

    public function removeNotificationUser(NotificationUser $notificationUser): static
    {
        if ($this->notificationUsers->removeElement($notificationUser)) {
            // set the owning side to null (unless already changed)
            if ($notificationUser->getUser() === $this) {
                $notificationUser->setUser(null);
            }
        }

        return $this;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): static
    {
        $this->search = $search;

        return $this;
    }

    public function getOneSignalAppId(): ?string
    {
        return $this->oneSignalAppId;
    }

    public function setOneSignalAppId(?string $oneSignalAppId): static
    {
        $this->oneSignalAppId = $oneSignalAppId;

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setUser($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getUser() === $this) {
                $payment->setUser(null);
            }
        }

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function isIsVatEnabled(): ?bool
    {
        return $this->isVatEnabled;
    }

    public function setIsVatEnabled(bool $isVatEnabled): static
    {
        $this->isVatEnabled = $isVatEnabled;

        return $this;
    }

}
