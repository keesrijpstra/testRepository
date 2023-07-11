<?php

namespace App\Entity;

use App\Repository\SymfonyConsoleMakeEntityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SymfonyConsoleMakeEntityRepository::class)]
class SymfonyConsoleMakeEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 255)]
    private ?string $MicroPost = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMicroPost(): ?string
    {
        return $this->MicroPost;
    }

    public function setMicroPost(string $MicroPost): self
    {
        $this->MicroPost = $MicroPost;

        return $this;
    }
}
