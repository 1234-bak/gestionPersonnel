<?php

namespace App\Entity;

use App\Repository\FileDecesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileDecesRepository::class)]
class FileDeces
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\ManyToOne(inversedBy: 'fichierdeces')]
    private ?Declaration $declaration = null;

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

    public function getDeclaration(): ?Declaration
    {
        return $this->declaration;
    }

    public function setDeclaration(?Declaration $declaration): self
    {
        $this->declaration = $declaration;

        return $this;
    }
}
