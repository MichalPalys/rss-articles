<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createDate;

    /**
     * Many Comments has One User.
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $maker;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(max=200)
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $article;

    public function getId()
    {
        return $this->id;
    }

    public function getCreateDate(): ?\DateTimeInterface
    {
        return $this->createDate;
    }

    public function setCreateDate(\DateTimeInterface $createDate): self
    {
        $this->createDate = $createDate;

        return $this;
    }

    public function getMaker(): ?User
    {
        return $this->maker;
    }

    public function setMaker(?User $maker): self
    {
        $this->maker = $maker;

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

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }
}
