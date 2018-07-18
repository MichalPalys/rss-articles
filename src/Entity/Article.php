<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 * @Vich\Uploadable
 */
class Article
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * One Article has One Photo.
     * @ORM\OneToOne(targetEntity="Photo", cascade={"persist"})
     * @ORM\JoinColumn(name="photo_id", referencedColumnName="id")
     */
    private $photo;

    /**
     * @Vich\UploadableField(mapping="article_image", fileNameProperty="photo")
     * @var File
     */
    private $photoFile;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $externalId;

    /**
     * @ORM\Column(type="string", length=256)
     * @Assert\NotBlank(message="article.title.not_blank", groups={"admin"})
     * @Assert\Length(min=3, groups={"admin"})
     * @Assert\Length(max=256, groups={"admin"})
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="article.content.not_blank", groups={"admin"})
     * @Assert\Length(min=3, groups={"admin"})
     * @Assert\Length(max=2000, groups={"admin"})
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="article.pub_date.not_blank", groups={"admin"})
     */
    private $pubDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $insertDate;

    /**
     * @ORM\Column(type="string", length=512)
     */
    private $slug;

    public function getId()
    {
        return $this->id;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): self
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPubDate(): ?\DateTimeInterface
    {
        return $this->pubDate;
    }

    public function setPubDate(?\DateTimeInterface $pubDate): self
    {
        $this->pubDate = $pubDate;

        return $this;
    }

    public function getInsertDate(): ?\DateTimeInterface
    {
        return $this->insertDate;
    }

    public function setInsertDate(?\DateTimeInterface $insertDate): self
    {
        $this->insertDate = $insertDate;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function setPhoto(?Photo $photo)
    {
        $this->photo = $photo;
    }

    public function getPhoto(): ?Photo
    {
        return $this->photo;
    }

    public function setPhotoFile(File $image = null)
    {
        $this->photoFile = $image;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($image) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->insertDate = new \DateTime('now');
        }
    }

    public function getPhotoFile()
    {
        return $this->photoFile;
    }
}
