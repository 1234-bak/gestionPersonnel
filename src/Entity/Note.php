<?php

namespace App\Entity;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\NoteRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
class Note
{
    public const STATUT_EN_ATTENTE = 'en attente';
    public const STATUT_DIFFUSEE = 'diffusée';

    public function someMethod()
    {
        $statut = self::STATUT_EN_ATTENTE;
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $objet = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numref = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'note')]   
    private ?Personne $personne = null;

    // #[ORM\Column(length: 255, nullable: true)]
    // private ?string $part1 = null;

    // #[ORM\Column(length: 255, nullable: true)]
    // private ?string $part2 = null;

    // #[ORM\Column(length: 255, nullable: true)]
    // private ?string $part3 = null;

    // #[ORM\Column(length: 255, nullable: true)]
    // private ?string $part4 = null;

    // #[ORM\Column(length: 255, nullable: true)]
    // private ?string $part5 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pj = null;

    #[ORM\ManyToMany(targetEntity: Personne::class, inversedBy: 'notes')]
    private Collection $destinataire;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = null;

    #[ORM\ManyToMany(targetEntity: Personne::class, inversedBy: 'noteduservice')]
    private Collection $service;

    #[ORM\ManyToMany(targetEntity: Personne::class, inversedBy: 'notesd')]
    private Collection $sousdirection;

    #[ORM\ManyToMany(targetEntity: Personne::class, inversedBy: 'notedir')]
    private Collection $direction;

    public function __construct()
    {
        $this->destinataire = new ArrayCollection();
        $this->service = new ArrayCollection();
        $this->sousdirection = new ArrayCollection();
        $this->direction = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObjet(): ?string
    {
        return $this->objet;
    }

    public function setObjet(string $objet): self
    {
        $this->objet = $objet;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getNumref(): ?string
    {
        return $this->numref;
    }

    public function setNumref(string $numref): self
    {
        $this->numref = $numref;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getFormattedDate(): string
    {
        $carbonDate = Carbon::instance($this->date);
        $formattedDate = $carbonDate->locale('fr')->isoFormat('D MMMM YYYY');
        return ucfirst($formattedDate);
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

    // public function getPart1(): ?string
    // {
    //     return $this->part1;
    // }

    // public function setPart1(?string $part1): self
    // {
    //     $this->part1 = $part1;

    //     return $this;
    // }

    // public function getPart2(): ?string
    // {
    //     return $this->part2;
    // }

    // public function setPart2(?string $part2): self
    // {
    //     $this->part2 = $part2;

    //     return $this;
    // }

    // public function getPart3(): ?string
    // {
    //     return $this->part3;
    // }

    // public function setPart3(?string $part3): self
    // {
    //     $this->part3 = $part3;

    //     return $this;
    // }

    // public function getPart4(): ?string
    // {
    //     return $this->part4;
    // }

    // public function setPart4(?string $part4): self
    // {
    //     $this->part4 = $part4;

    //     return $this;
    // }

    // public function getPart5(): ?string
    // {
    //     return $this->part5;
    // }

    // public function setPart5(?string $part5): self
    // {
    //     $this->part5 = $part5;

    //     return $this;
    // }

    public function getPj(): ?string
    {
        return $this->pj;
    }

    public function setPj(?string $pj): self
    {
        $this->pj = $pj;

        return $this;
    }

    /**
     * @return Collection<int, Personne>
     */
    public function getDestinataire(): Collection
    {
        return $this->destinataire;
    }

    public function addDestinataire(Personne $destinataire): self
    {
        if (!$this->destinataire->contains($destinataire)) {
            $this->destinataire->add($destinataire);
        }

        return $this;
    }

    public function removeDestinataire(Personne $destinataire): self
    {
        $this->destinataire->removeElement($destinataire);

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, Personne>
     */
    public function getService(): Collection
    {
        return $this->service;
    }

    public function addService(Personne $service): static
    {
        if (!$this->service->contains($service)) {
            $this->service->add($service);
        }

        return $this;
    }

    public function removeService(Personne $service): static
    {
        $this->service->removeElement($service);

        return $this;
    }

    /**
     * @return Collection<int, Personne>
     */
    public function getSousdirection(): Collection
    {
        return $this->sousdirection;
    }

    public function addSousdirection(Personne $sousdirection): static
    {
        if (!$this->sousdirection->contains($sousdirection)) {
            $this->sousdirection->add($sousdirection);
        }

        return $this;
    }

    public function removeSousdirection(Personne $sousdirection): static
    {
        $this->sousdirection->removeElement($sousdirection);

        return $this;
    }

    /**
     * @return Collection<int, Personne>
     */
    public function getDirection(): Collection
    {
        return $this->direction;
    }

    public function addDirection(Personne $direction): static
    {
        if (!$this->direction->contains($direction)) {
            $this->direction->add($direction);
        }

        return $this;
    }

    public function removeDirection(Personne $direction): static
    {
        $this->direction->removeElement($direction);

        return $this;
    }
}
