<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;


#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['post:read']]
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['post:read']]
        )
    ]
)]

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['post:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['post:read'])]
    private ?string $text = null;

    #[ORM\Column]
    #[Groups(['post:read'])]
    private ?\DateTime $postdate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['post:read'])]
    private ?string $img = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[Groups(['post:read'])]
    private ?User $author = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'likes')]
    #[Groups(['post:read'])]
    private Collection $likes;

    /**
     * Usuarios que han compartido este post
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'compartidos')]
    #[Groups(['post:read'])]
    private Collection $compartidos;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->compartidos = new ArrayCollection();
    }

    // --- GETTERS / SETTERS ---
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function getPostdate(): ?\DateTime
    {
        return $this->postdate;
    }

    public function setPostdate(\DateTime $postdate): static
    {
        $this->postdate = $postdate;
        return $this;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(?string $img): static
    {
        $this->img = $img;
        return $this;
    }

    
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(User $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
        }
        return $this;
    }

    public function removeLike(User $like): static
    {
        $this->likes->removeElement($like);
        return $this;
    }

    public function getCompartidos(): Collection
    {
        return $this->compartidos;
    }

    public function addCompartido(User $user): static
    {
        if (!$this->compartidos->contains($user)) {
            $this->compartidos->add($user);
            $user->addCompartido($this);
        }
        return $this;
    }

    public function removeCompartido(User $user): static
    {
        if ($this->compartidos->removeElement($user)) {
            $user->removeCompartido($this);
        }
        return $this;
    }
}
