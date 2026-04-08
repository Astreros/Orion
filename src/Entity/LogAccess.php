<?php

namespace App\Entity;

use App\Repository\LogAccessRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogAccessRepository::class)]
class LogAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $typeIdentification = null;

    #[ORM\Column]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTime $dateHeure = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(inversedBy: 'logAccesses')]
    private ?Code $code = null;

    #[ORM\ManyToOne(inversedBy: 'logAccesses')]
    private ?QRCode $QRCode = null;

    #[ORM\ManyToOne(inversedBy: 'logAccesses')]
    private ?Badge $badge = null;

    #[ORM\ManyToOne(inversedBy: 'logAccesses')]
    private ?Porte $porte = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeIdentification(): ?string
    {
        return $this->typeIdentification;
    }

    public function setTypeIdentification(string $typeIdentification): static
    {
        $this->typeIdentification = $typeIdentification;

        return $this;
    }

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateHeure(): ?\DateTime
    {
        return $this->dateHeure;
    }

    public function setDateHeure(\DateTime $dateHeure): static
    {
        $this->dateHeure = $dateHeure;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getCode(): ?Code
    {
        return $this->code;
    }

    public function setCode(?Code $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getQRCode(): ?QRCode
    {
        return $this->QRCode;
    }

    public function setQRCode(?QRCode $QRCode): static
    {
        $this->QRCode = $QRCode;

        return $this;
    }

    public function getBadge(): ?Badge
    {
        return $this->badge;
    }

    public function setBadge(?Badge $badge): static
    {
        $this->badge = $badge;

        return $this;
    }

    public function getPorte(): ?Porte
    {
        return $this->porte;
    }

    public function setPorte(?Porte $porte): static
    {
        $this->porte = $porte;

        return $this;
    }
}
