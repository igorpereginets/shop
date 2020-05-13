<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;

/**
 * @ApiFilter(SearchFilter::class, properties={
 *     "name": "partial",
 *     "description": "partial"
 * })
 * @ApiFilter(RangeFilter::class, properties={"price"})
 * @ApiFilter(DateFilter::class, properties={"publishedAt": DateFilter::EXCLUDE_NULL})
 * @ApiFilter(OrderFilter::class, properties={"name", "price", "publishedAt"})
 * @ApiFilter(PropertyFilter::class, arguments={
 *      "parameterName"="properties",
 *      "overrideDefaultProperties"=false,
 *      "whitelist"={"id", "category", "name", "description", "slug", "position", "price", "publishedAt"}
 * })
 * @ApiResource(
 *     attributes={"order"={"position": "DESC", "publishedAt": "DESC"}},
 *     normalizationContext={"groups"={"product:get"}},
 *     collectionOperations={
 *          "get",
 *          "post"={
 *              "security"="is_granted('ROLE_ADMIN')",
 *              "denormalization_context"={
 *                  "groups"={"product:post"}
 *              }
 *          }
 *     },
 *     itemOperations={
 *          "get",
 *          "put"={
 *              "security"="is_granted('ROLE_ADMIN')",
 *              "denormalization_context"={
 *                  "groups"={"product:post"}
 *              }
 *          }
 *     },
 *     subresourceOperations={
 *          "api_categories_products_get_subresource"={
 *              "normalization_context"={
 *                  "groups"={"category:products:get"}
 *              }
 *          }
 *     }
 * )
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @UniqueEntity(fields={"name"}, message="There is already a product with this name.")
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"product:get", "category:products:get"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min="5", max="100")
     * @Groups({"product:get", "product:post", "category:products:get"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Gedmo\Slug(handlers={
     *      @Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\TreeSlugHandler", options={
     *          @Gedmo\SlugHandlerOption(name="parentRelationField", value="category"),
     *          @Gedmo\SlugHandlerOption(name="separator", value="/")
     *     })
     * }, fields={"name"})
     * @Groups({"product:get", "category:products:get"})
     */
    private $slug;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(min="10", max="4096")
     * @Groups({"product:get", "product:post", "category:products:get"})
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type(type="boolean")
     * @Groups({"product:get", "product:post", "category:products:get"})
     */
    private $active = false;

    /**
     * @ORM\Column(type="integer")
     * @Assert\PositiveOrZero()
     * @Groups({"product:get", "product:post", "category:products:get"})
     */
    private $position = 1000;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank()
     * @Assert\PositiveOrZero()
     * @Groups({"product:get", "product:post", "category:products:get"})
     */
    private $price;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="published_at")
     * @Assert\Type(type="\DateTimeInterface")
     * @Gedmo\Timestampable(on="change", field="active", value=true)
     * @Groups({"product:get", "product:post", "category:products:get"})
     */
    private $publishedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"product:get", "product:post"})
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="product", orphanRemoval=true, cascade={"persist"})
     * @Groups("product:get")
     * @ApiSubresource()
     */
    private $comments;

    /**
     * @ORM\Column(type="datetime", name="updated_at")
     * @Gedmo\Timestampable()
     * @Groups({"product:get", "category:products:get"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @Gedmo\Timestampable(on="create")
     * @Groups({"product:get", "category:products:get"})
     */
    private $createdAt;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setProduct($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getProduct() === $this) {
                $comment->setProduct(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}
