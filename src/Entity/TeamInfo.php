<?php

namespace App\Entity;

use App\Repository\TeamInfoRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TeamInfoRepository::class)]
#[Assert\Cascade]
class TeamInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $hashkey = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastSavedAt = null;

    #[ORM\Column(length: 63)]
    #[Assert\NotBlank(message: 'error.verein.notblank')]
    private ?string $verein = null;

    #[ORM\Column(length: 31)]
    #[Assert\NotBlank(message: 'error.altersklasse.notblank')]
    private ?string $altersklasse = null;

    #[ORM\Column(length: 63)]
    #[Assert\NotBlank(message: 'error.ankunftszeit.notblank')]
    private ?string $ankunftszeit = null;

    #[ORM\Column(length: 63, nullable: true)]
    private ?string $teamname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoPath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picturePath = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Kontakt $kontakt = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'error.anzahl.notblank')]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $spielerVegan = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'error.anzahl.notblank')]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $spielerFleisch = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'error.anzahl.notblank')]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $betreuerVegan = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'error.anzahl.notblank')]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $betreuerFleisch = null;

    #[ORM\Column]
    private int $gaeste = 0;

    #[ORM\Column(length: 1023, nullable: true)]
    private ?string $bemerkung = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?BankAccount $account = null;


    public function __construct() {
        $this->createdAt = new DateTimeImmutable();
        $this->hashkey = bin2hex(random_bytes(32));
        $this->kontakt = new Kontakt();
        $this->account = new BankAccount();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getHashkey(): ?string
    {
        return $this->hashkey;
    }

    public function getLastSavedAt(): ?\DateTimeImmutable
    {
        return $this->lastSavedAt;
    }

    public function setLastSavedAt(\DateTimeImmutable $lastSavedAt): static
    {
        $this->lastSavedAt = $lastSavedAt;

        return $this;
    }

    public function getVerein(): ?string
    {
        return $this->verein;
    }

    public function setVerein(string $verein): static
    {
        $this->verein = $verein;

        return $this;
    }

    public function getAltersklasse(): ?string
    {
        return $this->altersklasse;
    }

    public function setAltersklasse(string $altersklasse): static
    {
        $this->altersklasse = $altersklasse;

        return $this;
    }

    public function getTeamname(): ?string
    {
        return $this->teamname;
    }

    public function setTeamname(?string $teamname): static
    {
        $this->teamname = $teamname;

        return $this;
    }

    public function getAnkunftszeit(): ?string
    {
        return $this->ankunftszeit;
    }

    public function setAnkunftszeit(string $ankunftszeit): static
    {
        $this->ankunftszeit = $ankunftszeit;

        return $this;
    }

    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    public function setLogoPath(?string $logoPath): static
    {
        $this->logoPath = $logoPath;

        return $this;
    }

    public function getPicturePath(): ?string
    {
        return $this->picturePath;
    }

    public function setPicturePath(?string $picturePath): static
    {
        $this->picturePath = $picturePath;

        return $this;
    }


    public function getKontakt(): ?Kontakt
    {
        return $this->kontakt;
    }

    public function setKontakt(Kontakt $kontakt): static
    {
        $this->kontakt = $kontakt;

        return $this;
    }

    public function getSpielerVegan(): ?int
    {
        return $this->spielerVegan;
    }

    public function setSpielerVegan(int $spielerVegan): static
    {
        $this->spielerVegan = $spielerVegan;

        return $this;
    }

    public function getSpielerFleisch(): ?int
    {
        return $this->spielerFleisch;
    }

    public function setSpielerFleisch(int $spielerFleisch): static
    {
        $this->spielerFleisch = $spielerFleisch;

        return $this;
    }

    public function getBetreuerVegan(): ?int
    {
        return $this->betreuerVegan;
    }

    public function setBetreuerVegan(int $betreuerVegan): static
    {
        $this->betreuerVegan = $betreuerVegan;

        return $this;
    }

    public function getBetreuerFleisch(): ?int
    {
        return $this->betreuerFleisch;
    }

    public function setBetreuerFleisch(int $betreuerFleisch): static
    {
        $this->betreuerFleisch = $betreuerFleisch;

        return $this;
    }

    public function getGaeste(): int
    {
        return $this->gaeste;
    }

    public function setGaeste(int $gaeste): static
    {
        $this->gaeste = $gaeste;

        return $this;
    }

    public function getBemerkung(): ?string
    {
        return $this->bemerkung;
    }

    public function setBemerkung(string $bemerkung): static
    {
        $this->bemerkung = $bemerkung;

        return $this;
    }


    public function getAccount(): ?BankAccount
    {
        return $this->account;
    }

    public function setAccount(BankAccount $account): static
    {
        $this->account = $account;

        return $this;
    }

    public function getCost(): int {
        return 90 * (
            intval($this->spielerVegan) +
            intval($this->spielerFleisch) +
            intval($this->betreuerVegan) +
            intval($this->betreuerFleisch)
        );
    }
}
