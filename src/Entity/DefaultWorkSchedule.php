<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'default_work_schedule')]
class DefaultWorkSchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 10)]
    private ?string $dayOfWeek = null; // ذخیره‌سازی روزهای هفته (شنبه تا پنج‌شنبه)

    #[ORM\Column(type: "time")]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: "time")]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\ManyToOne(targetEntity: "User")]
    #[ORM\JoinColumn(name: "CreatedBy", referencedColumnName: "id", nullable: true)]
    private ?User $created = null;    
    
    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $published;    
    
    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDayOfWeek(): ?string
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(string $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getCreated(): ?User
    {
        return $this->created;
    }

    public function setCreated(?User $created): static
    {
        $this->created = $created;

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
    
}