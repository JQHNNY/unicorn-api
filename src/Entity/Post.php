<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['post'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['post'])]
    private ?string $author = null;

    #[ORM\Column(length: 255)]
    #[Groups(['post'])]
    private ?string $message = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    #[MaxDepth(1)]
    #[Groups(['post'])]
    private ?Unicorn $unicorn = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getUnicorn(): ?Unicorn
    {
        return $this->unicorn;
    }

    public function setUnicorn(?Unicorn $unicorn): self
    {
        $this->unicorn = $unicorn;

        return $this;
    }
}
