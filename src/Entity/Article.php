<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $publicationDate = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $image = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: ArticleCarrouselImage::class, orphanRemoval: true)]
    private Collection $articleCarrouselImages;

    public function __construct()
    {
        $this->articleCarrouselImages = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTimeInterface $publicationDate): self
    {
        $this->publicationDate = $publicationDate;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, ArticleCarrouselImage>
     */
    public function getArticleCarrouselImages(): Collection
    {
        return $this->articleCarrouselImages;
    }

    public function addArticleCarrouselImage(ArticleCarrouselImage $articleCarrouselImage): self
    {
        if (!$this->articleCarrouselImages->contains($articleCarrouselImage)) {
            $this->articleCarrouselImages->add($articleCarrouselImage);
            $articleCarrouselImage->setArticle($this);
        }

        return $this;
    }

    public function removeArticleCarrouselImage(ArticleCarrouselImage $articleCarrouselImage): self
    {
        if ($this->articleCarrouselImages->removeElement($articleCarrouselImage)) {
            // set the owning side to null (unless already changed)
            if ($articleCarrouselImage->getArticle() === $this) {
                $articleCarrouselImage->setArticle(null);
            }
        }

        return $this;
    }
}
