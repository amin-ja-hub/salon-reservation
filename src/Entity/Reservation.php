<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Reservation")]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private ?int $id;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $cdate;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $udate;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $published;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $type;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $remove;

    #[ORM\ManyToOne(targetEntity: "User")]
    #[ORM\JoinColumn(name: "User", referencedColumnName: "id", nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: "Article")]
    #[ORM\JoinColumn(name: "Article", referencedColumnName: "id", nullable: true)]
    private ?Article $article= null;
    
    #[ORM\ManyToOne(targetEntity: "Article")]
    #[ORM\JoinColumn(name: "ArticleChild", referencedColumnName: "id", nullable: true)]
    private ?Article $articleChild= null;
 
    #[ORM\Column(type: "string", length: 256, nullable: true)]
    private ?string $reservationDateTime;    
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCdate(): ?\DateTimeInterface
    {
        return $this->cdate;
    }

    public function setCdate(?\DateTimeInterface $cdate): static
    {
        $this->cdate = $cdate;

        return $this;
    }

    public function getUdate(): ?\DateTimeInterface
    {
        return $this->udate;
    }

    public function setUdate(?\DateTimeInterface $udate): static
    {
        $this->udate = $udate;

        return $this;
    }

    public function getPublished(): ?int
    {
        return $this->published;
    }

    public function setPublished(?int $published): static
    {
        $this->published = $published;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRemove(): ?int
    {
        return $this->remove;
    }

    public function setRemove(?int $remove): static
    {
        $this->remove = $remove;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getArticleChild(): ?Article
    {
        return $this->articleChild;
    }

    public function setArticleChild(?Article $articleChild): static
    {
        $this->articleChild = $articleChild;

        return $this;
    }

    public function getReservationDateTime(): ?string
    {
        return $this->reservationDateTime;
    }

    public function setReservationDateTime(?string $reservationDateTime): static
    {
        $this->reservationDateTime = $reservationDateTime;

        return $this;
    }    
}
