<?php

namespace App\Entity;

use App\Repository\LaageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LaageRepository::class)
 */
class Laage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity=Julekalender::class, inversedBy="laager")
     * @ORM\JoinColumn(nullable=false)
     */
    private $julekalender;

    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getJulekalender(): ?Julekalender
    {
        return $this->julekalender;
    }

    public function setJulekalender(?Julekalender $julekalender): self
    {
        $this->julekalender = $julekalender;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
