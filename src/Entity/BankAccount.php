<?php

namespace App\Entity;

use App\Repository\KontaktRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: KontaktRepository::class)]
class BankAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 63)]
    #[Assert\NotBlank(message: 'error.iban.notblank')]
    #[Assert\Iban(message: 'error.iban.valid')]
    private ?string $iban = null;

    #[ORM\Column(length: 63)]
    #[Assert\NotBlank(message: 'error.bic.notblank')]
    #[Assert\Bic(message: 'error.bic.valid')]
    private ?string $bic = null;

    #[ORM\Column(length: 63)]
    #[Assert\NotBlank(message: 'error.bank.notblank')]
    private ?string $bank = null;

    #[ORM\Column(length: 63)]
    #[Assert\NotBlank(message: 'error.kontoinhaber.notblank')]
    private ?string $kontoinhaber = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): static
    {
        $this->iban = $iban;

        return $this;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    public function setBic(string $bic): static
    {
        $this->bic = $bic;

        return $this;
    }

    public function getBank(): ?string
    {
        return $this->bank;
    }

    public function setBank(?string $bank): static
    {
        $this->bank = $bank;

        return $this;
    }

    public function getKontoinhaber(): ?string
    {
        return $this->kontoinhaber;
    }

    public function setKontoinhaber(?string $kontoinhaber): static
    {
        $this->kontoinhaber = $kontoinhaber;

        return $this;
    }
}