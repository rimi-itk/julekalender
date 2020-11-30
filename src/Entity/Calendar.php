<?php

namespace App\Entity;

use App\Repository\CalendarRepository;
use App\Traits\BlameableEntity;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=CalendarRepository::class)
 * @Vich\Uploadable()
 */
class Calendar
{
    use BlameableEntity;
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @Groups({"scene"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity=Scene::class, mappedBy="calendar", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"position": "ASC"})
     * @Assert\Valid()
     */
    private $scenes;

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
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    private $audio;

    /**
     * @Vich\UploadableField(mapping="audio", fileNameProperty="audio")
     *
     * @var File
     */
    private $audioFile;

    /**
     * @ORM\Column(type="boolean")
     */
    private $audioLoop;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $configuration;

    public function __construct()
    {
        $this->configuration = '# configuration';
        $this->image = new EmbeddedFile();
        $this->scenes = new ArrayCollection();
        $date = new DateTimeImmutable('1 December');
        for ($i = 0; $i < 24; ++$i) {
            $scene = (new Scene())
                ->setPosition($i)
                ->setDoNotOpenUntil($date)
                ->setContent(sprintf('%d. december', $i + 1))
                ->setCropBox(json_encode([
                    'left' => 250 * ($i % 6) + 50,
                    'top' => 250 * floor($i / 6) + 50,
                    'width' => 200,
                    'height' => 200,
                ], JSON_THROW_ON_ERROR))
                ->setConfiguration(Yaml::dump([
                    'content' => [
                        'style' => [
                            'background-color' => '#ffffff',
                        ],
                    ],
                ], PHP_INT_MAX));
            $this->addScene($scene);
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
     * @return Collection|Scene[]
     */
    public function getScenes(): Collection
    {
        return $this->scenes;
    }

    public function addScene(Scene $scene): self
    {
        if (!$this->scenes->contains($scene)) {
            $this->scenes[] = $scene;
            $scene->setCalendar($this);
        }

        return $this;
    }

    public function removeScene(Scene $scene): self
    {
        if ($this->scenes->removeElement($scene)) {
            // set the owning side to null (unless already changed)
            if ($scene->getCalendar() === $this) {
                $scene->setCalendar(null);
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

    public function getAudio(): ?string
    {
        return $this->audio;
    }

    public function setAudio(?string $audio): self
    {
        $this->audio = $audio;

        return $this;
    }

    /**
     * @param File $audioFile
     */
    public function setAudioFile(?File $audioFile = null)
    {
        $this->audioFile = $audioFile;

        if (null !== $audioFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    /**
     * @return File
     */
    public function getAudioFile(): ?File
    {
        return $this->audioFile;
    }

    public function getAudioLoop(): ?bool
    {
        return $this->audioLoop;
    }

    public function setAudioLoop(bool $audioLoop): self
    {
        $this->audioLoop = $audioLoop;

        return $this;
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
