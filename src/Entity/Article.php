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
     * One Article has One Photo.
     * @ORM\OneToOne(targetEntity="Photo", cascade={"persist"})
     * @ORM\JoinColumn(name="photo_id", referencedColumnName="id")
     */
    private $photo;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $externalId;

    /**
     * @ORM\Column(type="string", length=256)
     * @Assert\NotBlank(message="article.title.not_blank")
     * @Assert\Length(min=3)
     * @Assert\Length(max=256)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="article.content.not_blank")
     * @Assert\Length(min=3)
     * @Assert\Length(max=2000)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\IsTrue(message="article.pub_date.is_set")
     */
    private $pubDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\IsTrue(message="article.insert_date.is_set")
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

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
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

    public function getPubDate(): ?\DateTimeInterface
    {
        return $this->pubDate;
    }

    public function setPubDate(\DateTimeInterface $pubDate): self
    {
        $this->pubDate = $pubDate;

        return $this;
    }

    public function getInsertDate(): ?\DateTimeInterface
    {
        return $this->insertDate;
    }

    public function setInsertDate(\DateTimeInterface $insertDate): self
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
