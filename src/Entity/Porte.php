<?php

namespace App\Entity;

use App\Repository\PorteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PorteRepository::class)]
class Porte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $emplacement = null;

    #[ORM\Column]
    private array $rolesActif = [];

    #[ORM\Column]
    private ?bool $actif = null;

    /**
     * @var Collection<int, LogAccess>
     */
    #[ORM\OneToMany(targetEntity: LogAccess::class, mappedBy: 'porte')]
    private Collection $logAccesses;

    public function __construct()
    {
        $this->logAccesses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }

    public function setEmplacement(string $emplacement): static
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    public function getRolesActif(): array
    {
        return $this->rolesActif;
    }

    public function setRolesActif(array $rolesActif): static
    {
        $this->rolesActif = $rolesActif;

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
            $logAccess->setPorte($this);
        }

        return $this;
    }

    public function removeLogAccess(LogAccess $logAccess): static
    {
        if ($this->logAccesses->removeElement($logAccess)) {
            // set the owning side to null (unless already changed)
            if ($logAccess->getPorte() === $this) {
                $logAccess->setPorte(null);
            }
        }

        return $this;
    }
}
