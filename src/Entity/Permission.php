<?php

namespace App\Entity;

use Carbon\Carbon;
use App\Traits\TimeStampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PermissionRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
class Permission
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

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datedebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datefin = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datereprise = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $duree = null;

    #[ORM\Column(type: Types::TEXT,nullable: true)]
    private ?string $motif = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(length: 255)]
    private ?string $preuve = null;

    // #[ORM\ManyToOne(inversedBy: 'permissions')]
    // private ?TypePermission $type = null;

    #[ORM\ManyToOne(inversedBy: 'permission')]
    private ?Personne $personne = null;

    #[ORM\Column(length: 255)]
    private ?string $typepermission = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatedebut(): ?\DateTimeInterface
    {
        return $this->datedebut;
    }

    public function setDatedebut(\DateTimeInterface $datedebut): self
    {
        $this->datedebut = $datedebut;

        return $this;
    }

    public function getFormattedDatedebut(): string
    {
        $carbonDatedebut = Carbon::instance($this->datedebut);
        $formattedDatedebut = $carbonDatedebut->locale('fr')->isoFormat('D MMMM YYYY');
        return ucfirst($formattedDatedebut);
    }

    public function getDatefin(): ?\DateTimeInterface
    {
        return $this->datefin;
    }

    public function setDatefin(\DateTimeInterface $datefin): self
    {
        $this->datefin = $datefin;

        return $this;
    }

    public function getFormattedDatefin(): string
    {
        $carbonDatefin = Carbon::instance($this->datefin);
        $formattedDatefin = $carbonDatefin->locale('fr')->isoFormat('D MMMM YYYY');
        return ucfirst($formattedDatefin);
    }

    public function getDatereprise(): ?\DateTimeInterface
    {
        return $this->datereprise;
    }

    public function setDatereprise(\DateTimeInterface $datereprise): self
    {
        $this->datereprise = $datereprise;

        return $this;
    }

    public function getFormattedDatereprise(): string
    {
        $carbonDatereprise = Carbon::instance($this->datereprise);
        $formattedDatereprise = $carbonDatereprise->locale('fr')->isoFormat('D MMMM YYYY');
        return ucfirst($formattedDatereprise);
    }

    public function getDuree(): ?string
    {
        return $this->duree;
    }

    public function setDuree(string $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): self
    {
        $this->motif = $motif;

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

    public function getPreuve(): ?string
    {
        return $this->preuve;
    }

    public function setPreuve(?string $preuve): self
    {
        $this->preuve = $preuve;

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

    // public function getType(): ?TypePermission
    // {
    //     return $this->type;
    // }

    // public function setType(?TypePermission $type): self
    // {
    //     $this->type = $type;

    //     return $this;
    // }

    public function getTypepermission(): ?string
    {
        return $this->typepermission;
    }

    public function setTypepermission(string $typepermission): self
    {
        $this->typepermission = $typepermission;

        return $this;
    }
}
