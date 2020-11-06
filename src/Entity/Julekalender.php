<?php

namespace App\Entity;

use App\Repository\JulekalenderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=JulekalenderRepository::class)
 */
class Julekalender
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity=Laage::class, mappedBy="julekalender", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"position": "ASC"})
     */
    private $laager;

    public function __construct()
    {
        $this->laager = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|Laage[]
     */
    public function getLaager(): Collection
    {
        return $this->laager;
    }

    public function addLaager(Laage $laager): self
    {
        if (!$this->laager->contains($laager)) {
            $this->laager[] = $laager;
            $laager->setJulekalender($this);
        }

        return $this;
    }

    public function removeLaager(Laage $laager): self
    {
        if ($this->laager->removeElement($laager)) {
            // set the owning side to null (unless already changed)
            if ($laager->getJulekalender() === $this) {
                $laager->setJulekalender(null);
            }
        }

        return $this;
    }
}
