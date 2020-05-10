<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"user:get"}},
 *     collectionOperations={
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={"user:post"}
 *              }
 *          }
 *     },
 *     itemOperations={
 *          "get",
 *          "put"={
 *              "access_control"="is_granted('ROLE_ADMIN') or object == user",
 *              "denormalization_context"={
 *                  "groups"={"user:put"}
 *              }
 *          }
 *     },
 * )
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"username"}, message="There is already a User with such username.")
 * @UniqueEntity(fields={"email"}, message="There is already a User with such email.")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"user:get", "product:comments:get"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"user:get", "user:post", "product:comments:get"})
     * @Assert\NotBlank()
     * @Assert\Length(min="4", max="255")
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\NotBlank(groups={"user:post"})
     * @Assert\Length(min="5", max="255")
     * @Assert\Regex(
     *     pattern="/^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z]).{5,}$/",
     *     message="Password should contain at least 1 uppercase, 1 lowercase letter and 1 digit."
     * )
     * @Groups({"user:post", "user:put"})
     */
    private $plainPassword;

    /**
     * @Assert\NotBlank(groups={"user:post"})
     * @Assert\Expression(
     *     expression="this.getPlainPassword() === this.getRetypedPlainPassword()",
     *     message="Retyped password does not match."
     * )
     * @Groups({"user:post", "user:put"})
     */
    private $retypedPlainPassword;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Groups({"user:post", "user:put"})
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:get", "user:post", "user:put", "product:comments:get"})
     * @Assert\NotBlank()
     * @Assert\Length(min="4", max="255")
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type(type="boolean")
     * @Groups({"user:get", "user:post", "product:comments:get"})
     */
    private $active = false;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Type("\DateTimeInterface")
     * @Groups({"user:post", "user:put", "user:get"})
     */
    private $birthday;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="user", orphanRemoval=true, cascade={"persist"})
     * @Groups("user:get")
     */
    private $comments;

    /**
     * @ORM\Column(type="datetime", name="updated_at")
     * @Gedmo\Timestampable()
     * @Groups({"user:get", "product:comments:get"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @Gedmo\Timestampable(on="create")
     * @Groups({"user:get", "product:comments:get"})
     */
    private $createdAt;

    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getRetypedPlainPassword(): ?string
    {
        return $this->retypedPlainPassword;
    }

    public function setRetypedPlainPassword(?string $retypedPlainPassword): self
    {
        $this->retypedPlainPassword = $retypedPlainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
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

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return (string) $this->username;
    }
}
