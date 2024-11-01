<?php

namespace App\Entity;

use App\Repository\KontaktRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: KontaktRepository::class)]
class Kontakt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 63)]
    #[Assert\NotBlank(message: 'error.vorname.notblank')]
    private ?string $vorname = null;

    #[ORM\Column(length: 63)]
    #[Assert\NotBlank(message: 'error.nachname.notblank')]
    private ?string $nachname = null;

    #[ORM\Column(length: 63)]
    #[Assert\NotBlank(message: 'error.email.notblank')]
    #[Assert\Email(message: 'error.email.valid')]
    private ?string $email = null;

    #[ORM\Column(length: 63)]
    #[Assert\NotBlank(message: 'error.phone.notblank')]
    private ?string $phone = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }
}
