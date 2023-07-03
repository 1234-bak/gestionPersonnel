<?php

namespace App\Entity;

use App\Repository\DeclarationRepository;
use App\Traits\TimeStampTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preuve = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $nomconcerne = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $prenomconcerne = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datedeclaration = null;

    #[ORM\ManyToOne(inversedBy: 'declarations')]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?Typedeclaration $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $programmeobsq = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $hasProgrammeObsq = null;

    #[ORM\ManyToOne(inversedBy: 'declaration')]
    private ?Personne $personne = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPreuve(): ?string
    {
        return $this->preuve;
    }

    public function setPreuve(?string $preuve): self
    {
        $this->preuve = $preuve;

        return $this;
    }

    public function getNomconcerne(): ?string
    {
        return $this->nomconcerne;
    }

    public function setNomconcerne(string $nomconcerne): self
    {
        $this->nomconcerne = $nomconcerne;

        return $this;
    }

    public function getPrenomconcerne(): ?string
    {
        return $this->prenomconcerne;
    }

    public function setPrenomconcerne(string $prenomconcerne): self
    {
        $this->prenomconcerne = $prenomconcerne;

        return $this;
    }

    public function getDatedeclaration(): ?\DateTimeInterface
    {
        return $this->datedeclaration;
    }

    public function setDatedeclaration(\DateTimeInterface $datedeclaration): self
    {
        $this->datedeclaration = $datedeclaration;

        return $this;
    }

    public function getType(): ?Typedeclaration
    {
        return $this->type;
    }

    public function setType(?Typedeclaration $type): self
    {
        $this->type = $type;

        return $this;
    }

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
}
