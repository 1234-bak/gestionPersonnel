<?php

namespace App\Entity;

use App\Traits\TimeStampTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SignatureRepository;

#[ORM\Entity(repositoryClass: SignatureRepository::class)]
class Signature
{
    use TimeStampTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    // #[ORM\ManyToOne(inversedBy: 'signature')]
    // private ?Personne $personne = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    // public function getPersonne(): ?Personne
    // {
    //     return $this->personne;
    // }

    // public function setPersonne(?Personne $personne): self
    // {
    //     $this->personne = $personne;

    //     return $this;
    // }
}
