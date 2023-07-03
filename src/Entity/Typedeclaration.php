<?php

namespace App\Entity;

use App\Traits\TimeStampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\TypedeclarationRepository;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: TypedeclarationRepository::class)]
class Typedeclaration
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

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Declaration::class)]
    private Collection $declarations;

    public function __construct()
    {
        $this->declarations = new ArrayCollection();
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
     * @return Collection<int, Declaration>
     */
    public function getDeclarations(): Collection
    {
        return $this->declarations;
    }

    public function addDeclaration(Declaration $declaration): self
    {
        if (!$this->declarations->contains($declaration)) {
            $this->declarations->add($declaration);
            $declaration->setType($this);
        }

        return $this;
    }

    public function removeDeclaration(Declaration $declaration): self
    {
        if ($this->declarations->removeElement($declaration)) {
            // set the owning side to null (unless already changed)
            if ($declaration->getType() === $this) {
                $declaration->setType(null);
            }
        }

        return $this;
    }
}
