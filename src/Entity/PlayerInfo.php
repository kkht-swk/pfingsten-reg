<?php

namespace App\Entity;

use App\Repository\PlayerInfoRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlayerInfoRepository::class)]
#[Assert\Cascade]
class PlayerInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $hashkey = null;

    #[ORM\Column(length: 63)]
    #[Assert\NotBlank(message: 'error.vorname.notblank')]
    private ?string $vorname = null;

    #[ORM\Column(length: 63)]
    #[Assert\NotBlank(message: 'error.nachname.notblank')]
    private ?string $nachname = null;

    #[ORM\Column(length: 31)]
    #[Assert\NotBlank(message: 'error.altersklasse.notblank')]
    private ?string $altersklasse = null;

    #[ORM\Column(length: 31)]
    #[Assert\NotBlank(message: 'error.nahrung.notblank')]
    private ?string $nahrung = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Kontakt $kontakt = null;

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


    public function getVorname(): ?string
    {
        return $this->vorname;
    }

    public function setVorname(string $vorname): static
    {
        $this->vorname = $vorname;

        return $this;
    }

    public function getNachname(): ?string
    {
        return $this->nachname;
    }

    public function setNachname(string $nachname): static
    {
        $this->nachname = $nachname;

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

    public function getNahrung(): ?string
    {
        return $this->nahrung;
    }

    public function setNahrung(string $nahrung): static
    {
        $this->nahrung = $nahrung;

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

    public function getAccount(): ?BankAccount
    {
        return $this->account;
    }

    public function setAccount(BankAccount $account): static
    {
        $this->account = $account;

        return $this;
    }
}
