<?php

namespace App\Entity;

use App\Repository\QRCodeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QRCodeRepository::class)]
class QRCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $codeQR = null;

    #[ORM\Column]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column]
    private ?\DateTime $dateExpiration = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\OneToOne]
    private ?Utilisateur $utilisateur = null;

    /**
     * @var Collection<int, LogAccess>
     */
    #[ORM\OneToMany(targetEntity: LogAccess::class, mappedBy: 'QRCode')]
    private Collection $logAccesses;

    public function __construct()
    {
        $this->logAccesses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeQR(): ?string
    {
        return $this->codeQR;
    }

    public function setCodeQR(string $codeQR): static
    {
        $this->codeQR = $codeQR;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateExpiration(): ?\DateTime
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(\DateTime $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * @return Collection<int, LogAccess>
     */
    public function getLogAccesses(): Collection
    {
        return $this->logAccesses;
    }

    public function addLogAccess(LogAccess $logAccess): static
    {
        if (!$this->logAccesses->contains($logAccess)) {
            $this->logAccesses->add($logAccess);
            $logAccess->setQRCode($this);
        }

        return $this;
    }

    public function removeLogAccess(LogAccess $logAccess): static
    {
        if ($this->logAccesses->removeElement($logAccess)) {
            // set the owning side to null (unless already changed)
            if ($logAccess->getQRCode() === $this) {
                $logAccess->setQRCode(null);
            }
        }

        return $this;
    }
}
