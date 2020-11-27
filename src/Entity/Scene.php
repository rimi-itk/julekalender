<?php

namespace App\Entity;

use App\Repository\SceneRepository;
use DateTimeImmutable;
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
 * @ORM\Entity(repositoryClass=SceneRepository::class)
 * @Vich\Uploadable()
 */
class Scene
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @Groups("scene")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Groups("scene")
     */
    private $content;

    /**
     * @ORM\Embedded(class="Vich\UploaderBundle\Entity\File")
     * @Groups({"scene"})
     */
    private $contentImage;

    /**
     * @Vich\UploadableField(mapping="images", fileNameProperty="contentImage.name", size="contentImage.size", mimeType="contentImage.mimeType", originalName="contentImage.originalName", dimensions="contentImage.dimensions")
     *
     * @var File
     */
    private $contentImageFile;

    /**
     * @ORM\Embedded(class="Vich\UploaderBundle\Entity\File")
     * @Groups({"scene"})
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="images", fileNameProperty="image.name", size="image.size", mimeType="image.mimeType", originalName="image.originalName", dimensions="image.dimensions")
     *
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\ManyToOne(targetEntity=Calendar::class, inversedBy="scenes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $calendar;

    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @ORM\Column(type="text")
     */
    private $configuration;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("scene")
     */
    private $doNotOpenUntil;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("scene")
     */
    private $openedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cropBox;

    public function __construct()
    {
        $this->image = new EmbeddedFile();
    }

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

    public function getCalendar(): ?Calendar
    {
        return $this->calendar;
    }

    public function setCalendar(?Calendar $calendar): self
    {
        $this->calendar = $calendar;

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

    public function setConfiguration(?string $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    public function getContentImage(): ?EmbeddedFile
    {
        return $this->contentImage;
    }

    public function setContentImage(EmbeddedFile $contentImage): self
    {
        $this->contentImage = $contentImage;

        return $this;
    }

    /**
     * @param File $contentImageFile
     */
    public function setContentImageFile(?File $contentImageFile = null)
    {
        $this->contentImageFile = $contentImageFile;

        if (null !== $contentImageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    /**
     * @return File
     */
    public function getContentImageFile(): ?File
    {
        return $this->contentImageFile;
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

    public function getDoNotOpenUntil(): ?\DateTimeInterface
    {
        return $this->doNotOpenUntil;
    }

    public function setDoNotOpenUntil(\DateTimeInterface $doNotOpenUntil): self
    {
        $this->doNotOpenUntil = $doNotOpenUntil;

        return $this;
    }

    public function getOpenedAt(): ?\DateTimeInterface
    {
        return $this->openedAt;
    }

    public function setOpenedAt(?\DateTimeInterface $openedAt): self
    {
        $this->openedAt = $openedAt;

        return $this;
    }

    public function getCropBox(): ?string
    {
        return $this->cropBox ?? json_encode([
                'left' => 0,
                'top' => 0,
                'width' => 200,
                'height' => 200,
            ], JSON_THROW_ON_ERROR);
    }

    public function getCropBoxAsArray(): ?array
    {
        try {
            return Yaml::parse($this->getCropBox() ?? '');
        } catch (ParseException $exception) {
            return null;
        }
    }

    public function setCropBox(?string $cropBox): self
    {
        $this->cropBox = $cropBox;

        return $this;
    }
}
