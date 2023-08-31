<?php

namespace App\Entity;

use Carbon\Carbon;
use App\Entity\Permission;
use App\Entity\Declaration;
use App\Traits\TimeStampTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PersonneRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonneRepository::class)]
class Personne
{
    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 15)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $matricule = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $civilite = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $structure = null;

    #[ORM\Column(length: 20, type: "datetime")]
    #[Assert\NotNull(message: "Veuillez renseigner la date")]
    private ?\DateTimeInterface $datenaiss = null;
    
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $lieunaiss = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $nompere = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $nommere = null;

    #[ORM\Column(length: 2)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $nbreenfant = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $grade = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $sexe = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Veuillez renseigner ce champ")]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'personnes')]
    private Collection $role;

    #[ORM\ManyToOne(targetEntity: Direction::class, inversedBy: 'personnes', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Direction $direction = null;

    #[ORM\ManyToOne(targetEntity: SousDirection::class, inversedBy: 'personnes', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?SousDirection $sousdirection = null;

    #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: 'personnes', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Service $service = null;

    #[ORM\OneToMany(mappedBy: 'personne', targetEntity: Declaration::class)]
    private Collection $declaration;

    #[ORM\OneToMany(mappedBy: 'personne', targetEntity: Permission::class)]
    private Collection $permission;

    #[ORM\OneToMany(mappedBy: 'personne', targetEntity: Note::class)]
    private Collection $note;

    // #[ORM\OneToMany(mappedBy: 'personne', targetEntity: Signature::class)]
    // private Collection $signature;

    #[ORM\ManyToOne(targetEntity: Fonction::class, inversedBy: 'personnes')]
    #[ORM\JoinColumn(name: 'fonction_id', referencedColumnName: 'id', nullable: true)]
    private ?Fonction $fonction = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $signature = null;

    #[ORM\ManyToMany(targetEntity: Note::class, mappedBy: 'destinataire')]
    private Collection $notes;

    #[ORM\ManyToMany(targetEntity: Notification::class, mappedBy: 'destinataire')]
    private Collection $notifications;



    public function __construct()
    {
        
        $this->role = new ArrayCollection();
        $this->declaration = new ArrayCollection();
        $this->permission = new ArrayCollection(); 
        $this->note = new ArrayCollection();
        // $this->signature = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(string $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(string $civilite): self
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getStructure(): ?string
    {
        return $this->structure;
    }

    public function setStructure(string $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    public function getDateNaiss(): ?\DateTimeInterface
    {
        return $this->datenaiss;
    }

    public function setDateNaiss(\DateTimeInterface $datenaiss): self
    {
        $this->datenaiss = $datenaiss;

        return $this;
    }

    public function getFormattedDateNaiss(): string
    {
        $carbonDatenaiss = Carbon::instance($this->datenaiss);
        $formattedDatenaiss = $carbonDatenaiss->locale('fr')->isoFormat('D MMMM YYYY');
        return ucfirst($formattedDatenaiss);
    }

    public function getLieuNaiss(): ?string
    {
        return $this->lieunaiss;
    }

    public function setLieuNaiss(string $lieunaiss): self
    {
        $this->lieunaiss = $lieunaiss;

        return $this;
    }

    public function getNomPere(): ?string
    {
        return $this->nompere;
    }

    public function setNomPere(string $nompere): self
    {
        $this->nompere = $nompere;

        return $this;
    }

    public function getNomMere(): ?string
    {
        return $this->nommere;
    }

    public function setNomMere(string $nommere): self
    {
        $this->nommere = $nommere;

        return $this;
    }

    public function getNbreEnfant(): ?string
    {
        return $this->nbreenfant;
    }

    public function setNbreEnfant(string $nbreenfant): self
    {
        $this->nbreenfant = $nbreenfant;

        return $this;
    }

    public function getGrade(): ?string
    {
        return $this->grade;
    }

    public function setGrade(string $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(string $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getRole(): Collection
    {
        return $this->role;
    }

    public function addRole(Role $role): self
    {
        if (!$this->role->contains($role)) {
            $this->role[] = $role;
            $role->addPersonne($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->role->removeElement($role)) {
            $role->removePersonne($this);
        }

        return $this;
    }

    public function getDirection(): ?Direction
    {
        return $this->direction;
    }

    public function setDirection(Direction $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    public function getSousdirection(): ?SousDirection
    {
        return $this->sousdirection;
    }

    public function setSousdirection(SousDirection $sousdirection): self
    {
        $this->sousdirection = $sousdirection;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    /**
     * @return Collection<int, Declaration>
     */
    public function getDeclaration(): Collection
    {
        return $this->declaration;
    }

    public function addDeclaration(Declaration $declaration): self
    {
        if (!$this->declaration->contains($declaration)) {
            $this->declaration->add($declaration);
            $declaration->setPersonne($this);
        }

        return $this;
    }

    public function removeDeclaration(Declaration $declaration): self
    {
        if ($this->declaration->removeElement($declaration)) {
            // set the owning side to null (unless already changed)
            if ($declaration->getPersonne() === $this) {
                $declaration->setPersonne(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermission(): Collection
    {
        return $this->permission;
    }

    public function addPermission(Permission $permission): self
    {
        if (!$this->permission->contains($permission)) {
            $this->permission->add($permission);
            $permission->setPersonne($this);
        }

        return $this;
    }

    public function removePermission(Permission $permission): self
    {
        if ($this->permission->removeElement($permission)) {
            // set the owning side to null (unless already changed)
            if ($permission->getPersonne() === $this) {
                $permission->setPersonne(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNote(): Collection
    {
        return $this->note;
    }

    public function addNote(Note $note): self
    {
        if (!$this->note->contains($note)) {
            $this->note->add($note);
            $note->setPersonne($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->note->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getPersonne() === $this) {
                $note->setPersonne(null);
            }
        }

        return $this;
    }

    // /**
    //  * @return Collection<int, Signature>
    //  */
    // public function getSignature(): Collection
    // {
    //     return $this->signature;
    // }

    // public function addSignature(Signature $signature): self
    // {
    //     if (!$this->signature->contains($signature)) {
    //         $this->signature->add($signature);
    //         $signature->setPersonne($this);
    //     }

    //     return $this;
    // }

    // public function removeSignature(Signature $signature): self
    // {
    //     if ($this->signature->removeElement($signature)) {
    //         // set the owning side to null (unless already changed)
    //         if ($signature->getPersonne() === $this) {
    //             $signature->setPersonne(null);
    //         }
    //     }

    //     return $this;
    // }

    public function getFonction(): ?Fonction
    {
        return $this->fonction;
    }

    public function setFonction(?Fonction $fonction): self
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): self
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->addDestinataire($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            $notification->removeDestinataire($this);
        }

        return $this;
    }
}
