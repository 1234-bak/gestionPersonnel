<?php

namespace App\Entity;

use Carbon\Carbon;
use App\Traits\TimeStampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\DeclarationRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DeclarationRepository::class)]
class Declaration
{
    use TimeStampTrait;
    public const STATUT_EN_ATTENTE = 'en attente';
    public const STATUT_ANNULEE = 'annulée';
    public const STATUT_VALIDEE = 'validée';

    public const STATUT_REJETEE = 'rejeté';

    // ...

    public function someMethod()
    {
        $statut = self::STATUT_EN_ATTENTE;
        // Utilisez $statut comme vous le souhaitez
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $programmeobsq = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $hasProgrammeObsq = null;

    #[ORM\ManyToOne(inversedBy: 'declaration')]
    private ?Personne $personne = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $enfant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $parent = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datenaiss = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datedeces = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $matriculedeces = null;

    // #[ORM\Column(length: 255, nullable: true)]
    // private ?string $fichiernaiss = null;

    // #[ORM\Column(length: 255, nullable: true)]
    // private ?string $fichierdeces = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typedeclaration = null;

    #[ORM\OneToMany(mappedBy: 'declaration', targetEntity: FileNaiss::class)]
    private Collection $fichiernaiss;

    #[ORM\OneToMany(mappedBy: 'declaration', targetEntity: FileDeces::class)]
    private Collection $fichierdeces;

    public function __construct()
    {
        $this->fichiernaiss = new ArrayCollection();
        $this->fichierdeces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFormattedDatenaiss(): string
    {
        $carbonDatenaiss = Carbon::instance($this->datenaiss);
        $formattedDatenaiss = $carbonDatenaiss->locale('fr')->isoFormat('D MMMM YYYY');
        return ucfirst($formattedDatenaiss);
    }
    public function getFormattedDatedeces(): string
    {
        $carbonDatedeces = Carbon::instance($this->datedeces);
        $formattedDatedeces = $carbonDatedeces->locale('fr')->isoFormat('D MMMM YYYY');
        return ucfirst($formattedDatedeces);
    }

    // public function getType(): ?Typedeclaration
    // {
    //     return $this->type;
    // }

    // public function setType(?Typedeclaration $type): self
    // {
    //     $this->type = $type;

    //     return $this;
    // }

    public function getProgrammeobsq(): ?string
    {
        return $this->programmeobsq;
    }

    public function setProgrammeobsq(?string $programmeobsq): self
    {
        $this->programmeobsq = $programmeobsq;

        return $this;
    }

    public function getHasProgrammeObsq(): ?bool
    {
        return $this->hasProgrammeObsq;
    }

    public function setHasProgrammeObsq(?bool $hasProgrammeObsq): self
    {
        $this->hasProgrammeObsq = $hasProgrammeObsq;

        return $this;
    }

    public function getPersonne(): ?Personne
    {
        return $this->personne;
    }

    public function setPersonne(?Personne $personne): self
    {
        $this->personne = $personne;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getEnfant(): ?string
    {
        return $this->enfant;
    }

    public function setEnfant(?string $enfant): self
    {
        $this->enfant = $enfant;

        return $this;
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function setParent(?string $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getDatenaiss(): ?\DateTimeInterface
    {
        return $this->datenaiss;
    }

    public function setDatenaiss(?\DateTimeInterface $datenaiss): self
    {
        $this->datenaiss = $datenaiss;

        return $this;
    }

    public function getDatedeces(): ?\DateTimeInterface
    {
        return $this->datedeces;
    }

    public function setDatedeces(?\DateTimeInterface $datedeces): self
    {
        $this->datedeces = $datedeces;

        return $this;
    }

    public function getMatriculedeces(): ?string
    {
        return $this->matriculedeces;
    }

    public function setMatriculedeces(?string $matriculedeces): self
    {
        $this->matriculedeces = $matriculedeces;

        return $this;
    }

    // public function getFichiernaiss(): ?string
    // {
    //     return $this->fichiernaiss;
    // }

    // public function setFichiernaiss(string $fichiernaiss): self
    // {
    //     $this->fichiernaiss = $fichiernaiss;

    //     return $this;
    // }

    // public function getFichierdeces(): ?string
    // {
    //     return $this->fichierdeces;
    // }

    // public function setFichierdeces(string $fichierdeces): self
    // {
    //     $this->fichierdeces = $fichierdeces;

    //     return $this;
    // }

    public function getTypedeclaration(): ?string
    {
        return $this->typedeclaration;
    }

    public function setTypedeclaration(string $typedeclaration): self
    {
        $this->typedeclaration = $typedeclaration;

        return $this;
    }

    /**
     * @return Collection<int, FileNaiss>
     */
    public function getFichiernaiss(): Collection
    {
        return $this->fichiernaiss;
    }

    public function addFichiernaiss(FileNaiss $fichiernaiss): self
    {
        if (!$this->fichiernaiss->contains($fichiernaiss)) {
            $this->fichiernaiss->add($fichiernaiss);
            $fichiernaiss->setDeclaration($this);
        }

        return $this;
    }

    public function removeFichiernaiss(FileNaiss $fichiernaiss): self
    {
        if ($this->fichiernaiss->removeElement($fichiernaiss)) {
            // set the owning side to null (unless already changed)
            if ($fichiernaiss->getDeclaration() === $this) {
                $fichiernaiss->setDeclaration(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FileDeces>
     */
    public function getFichierdeces(): Collection
    {
        return $this->fichierdeces;
    }

    public function addFichierdece(FileDeces $fichierdece): self
    {
        if (!$this->fichierdeces->contains($fichierdece)) {
            $this->fichierdeces->add($fichierdece);
            $fichierdece->setDeclaration($this);
        }

        return $this;
    }

    public function removeFichierdece(FileDeces $fichierdece): self
    {
        if ($this->fichierdeces->removeElement($fichierdece)) {
            // set the owning side to null (unless already changed)
            if ($fichierdece->getDeclaration() === $this) {
                $fichierdece->setDeclaration(null);
            }
        }

        return $this;
    }
}
