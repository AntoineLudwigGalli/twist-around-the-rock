<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $illustrationImageRight = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $illustrationImageLeft = null;

    #[ORM\Column]
    private ?bool $available = null;



    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;


    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Color $color = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Stone $stone = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductCarrouselImage::class, orphanRemoval: true)]
    private Collection $productCarrouselImages;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->productCarrouselImages = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): self
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getIllustrationImageRight(): ?string
    {
        return $this->illustrationImageRight;
    }

    public function setIllustrationImageRight(?string $illustrationImageRight): self
    {
        $this->illustrationImageRight = $illustrationImageRight;

        return $this;
    }

    public function getIllustrationImageLeft(): ?string
    {
        return $this->illustrationImageLeft;
    }

    public function setIllustrationImageLeft(?string $illustrationImageLeft): self
    {
        $this->illustrationImageLeft = $illustrationImageLeft;

        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): self
    {
        $this->available = $available;

        return $this;
    }


    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }





    public function getColor(): ?Color
    {
        return $this->color;
    }

    public function setColor(?Color $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getStone(): ?Stone
    {
        return $this->stone;
    }

    public function setStone(?Stone $stone): self
    {
        $this->stone = $stone;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, ProductCarrouselImage>
     */
    public function getProductCarrouselImages(): Collection
    {
        return $this->productCarrouselImages;
    }

    public function addProductCarrouselImage(ProductCarrouselImage $productCarrouselImage): self
    {
        if (!$this->productCarrouselImages->contains($productCarrouselImage)) {
            $this->productCarrouselImages->add($productCarrouselImage);
            $productCarrouselImage->setProduct($this);
        }

        return $this;
    }

    public function removeProductCarrouselImage(ProductCarrouselImage $productCarrouselImage): self
    {
        if ($this->productCarrouselImages->removeElement($productCarrouselImage)) {
            // set the owning side to null (unless already changed)
            if ($productCarrouselImage->getProduct() === $this) {
                $productCarrouselImage->setProduct(null);
            }
        }

        return $this;
    }


}
