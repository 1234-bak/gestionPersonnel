<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use App\Traits\TimeStampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    use TimeStampTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Personne::class, mappedBy: 'role')]
    private Collection $personnes;

    #[ORM\ManyToMany(targetEntity: Privilege::class, inversedBy: 'roles')]
    private Collection $privilege;

    public function __construct()
    {
        $this->personnes = new ArrayCollection();
        $this->privilege = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Personne>
     */
    public function getPersonnes(): Collection
    {
        return $this->personnes;
    }

    public function addPersonne(Personne $personne): self
    {
        if (!$this->personnes->contains($personne)) {
            $this->personnes->add($personne);
            $personne->addRole($this);
        }

        return $this;
    }

    public function removePersonne(Personne $personne): self
    {
        if ($this->personnes->removeElement($personne)) {
            $personne->removeRole($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Privilege>
     */
    public function getPrivilege(): Collection
    {
        return $this->privilege;
    }

    public function addPrivilege(Privilege $privilege): self
    {
        if (!$this->privilege->contains($privilege)) {
            $this->privilege->add($privilege);
        }

        return $this;
    }

    public function removePrivilege(Privilege $privilege): self
    {
        $this->privilege->removeElement($privilege);

        return $this;
    }
}
