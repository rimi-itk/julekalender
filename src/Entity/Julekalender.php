<?php

namespace App\Entity;

use App\Repository\JulekalenderRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=JulekalenderRepository::class)
 * @Vich\Uploadable()
 */
class Julekalender
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity=Laage::class, mappedBy="julekalender", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"position": "ASC"})
     * @Assert\Valid()
     */
    private $laager;

    /**
     * @ORM\Embedded(class="Vich\UploaderBundle\Entity\File")
     * @Assert\NotBlank()
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="images", fileNameProperty="image.name", size="image.size", mimeType="image.mimeType", originalName="image.originalName", dimensions="image.dimensions")
     *
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $configuration;

    public function __construct()
    {
        $this->configuration = '# configuration';
        $this->image = new EmbeddedFile();
        $this->laager = new ArrayCollection();
        $date = new DateTimeImmutable('1 December');
        for ($i = 0; $i < 24; ++$i) {
            $laage = (new Laage())
                ->setPosition($i)
                ->setDoNotOpenUntil($date)
                ->setContent(sprintf('%d. december', $i + 1))
                ->setConfiguration(Yaml::dump([
                    'style' => [
                        'left' => sprintf('%dpx', 250 * ($i % 6) + 50),
                        'top' => sprintf('%dpx', 250 * floor($i / 6) + 50),
                        'width' => '200px',
                        'height' => '200px',
                    ],
                    'content' => [
                        'style' => [
                            'background-color' => '#000000',
                        ],
                    ],
                ], PHP_INT_MAX));
            $this->addLaager($laage);
            $date = $date->add(new DateInterval('P1D'));
        }
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

    public function addLaager(Laage $laage): self
    {
        if (!$this->laager->contains($laage)) {
            $this->laager[] = $laage;
            $laage->setJulekalender($this);
        }

        return $this;
    }

    public function removeLaager(Laage $laage): self
    {
        if ($this->laager->removeElement($laage)) {
            // set the owning side to null (unless already changed)
            if ($laage->getJulekalender() === $this) {
                $laage->setJulekalender(null);
            }
        }

        return $this;
    }

    public function getImage(): ?EmbeddedFile
    {
        return $this->image;
    }

    public function setImage(EmbeddedFile $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @param File $imageFile
     */
    public function setImageFile(?File $imageFile = null)
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    /**
     * @return File
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getConfiguration(): ?string
    {
        return $this->configuration;
    }

    public function getConfigurationAsArray(): ?array
    {
        try {
            return Yaml::parse($this->getConfiguration());
        } catch (ParseException $exception) {
            return null;
        }
    }

    public function setConfiguration(string $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }
}
