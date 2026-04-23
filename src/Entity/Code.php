<?php

namespace App\Entity;

use App\Repository\CodeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CodeRepository::class)]
class Code
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $codePIN = null;

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
    #[ORM\OneToMany(targetEntity: LogAccess::class, mappedBy: 'code')]
    private Collection $logAccesses;

    public function __construct()
    {
        $this->logAccesses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodePIN(): ?int
    {
        return $this->codePIN;
    }

    public function setCodePIN(int $codePIN): static
    {
        $this->codePIN = $codePIN;

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
            $logAccess->setCode($this);
        }

        return $this;
    }

    public function removeLogAccess(LogAccess $logAccess): static
    {
        if ($this->logAccesses->removeElement($logAccess)) {
            // set the owning side to null (unless already changed)
            if ($logAccess->getCode() === $this) {
                $logAccess->setCode(null);
            }
        }

        return $this;
    }
}
