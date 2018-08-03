<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
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
     * Many Article has One Photo.
     * @ORM\ManyToOne(targetEntity="Photo", cascade={"persist"})
     * @ORM\JoinColumn(name="photo_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $photo;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
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
}
