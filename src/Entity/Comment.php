<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"comment:get"}},
 *     collectionOperations={
 *          "get",
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={"comment:post"}
 *              }
 *          }
 *     },
 *     itemOperations={
 *          "get",
 *          "put"={
 *              "security_post_denormalize"="(object.getUser() == user and previous_object.getUser() == user) or is_granted('ROLE_ADMIN')",
 *              "denormalization_context"={
 *                  "groups"={"comment:put"}
 *              }
 *          }
 *     },
 *     subresourceOperations={
 *          "api_products_comments_get_subresource"={
 *              "normalization_context"={
 *                  "groups"={"product:comments:get"}
 *              }
 *          }
 *     }
 * )
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("comment:get")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(max="4096")
     * @Groups({"comment:get", "comment:post", "comment:put", "product:comments:get"})
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity=Comment::class, inversedBy="children")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"comment:get", "comment:post"})
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="parent", orphanRemoval=true, cascade={"persist"})
     * @Groups({"comment:get", "product:comments:get"})
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"comment:get", "comment:post"})
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"comment:get", "product:comments:get"})
     */
    private $user;

    /**
     * @ORM\Column(type="datetime", name="updated_at")
     * @Gedmo\Timestampable()
     * @Assert\Type(type="\DateTimeInterface")
     * @Groups({"comment:get"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @Gedmo\Timestampable(on="create")
     * @Groups({"comment:get", "product:comments:get"})
     */
    private $createdAt;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function __toString()
    {
        return substr($this->content, 0, 15);
    }
}
