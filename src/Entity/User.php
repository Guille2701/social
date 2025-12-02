<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'author')]
    private Collection $posts;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'likes')]
    private Collection $likes;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'followers')]
    #[ORM\JoinTable(name: 'user_followers')]
    private Collection $following;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'following')]
    private Collection $followers;

    /**
     * @var Collection<int, Story>
     */
    #[ORM\OneToMany(targetEntity: Story::class, mappedBy: 'author')]
    private Collection $stories;

    /**
     * @var Collection<int, Story>
     */
    #[ORM\ManyToMany(targetEntity: Story::class, mappedBy: 'likes')]
    private Collection $storiesLikes;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\ManyToMany(targetEntity: Post::class, inversedBy: 'compartidos')]
    private Collection $compartidos;

    #[ORM\OneToMany(targetEntity: Conversation::class, mappedBy: 'user1')]
    private Collection $conversations;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'sender')]
    private Collection $myMessages;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->stories = new ArrayCollection();
        $this->storiesLikes = new ArrayCollection();
        $this->compartidos = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->myMessages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles);
    }

    public function setAdmin(bool $isAdmin): static
    {
        if ($isAdmin && !$this->isAdmin()) {
            $this->roles[] = 'ROLE_ADMIN';
        } elseif (!$isAdmin && $this->isAdmin()) {
            $this->roles = array_values(array_diff($this->roles, ['ROLE_ADMIN']));
        }

        return $this;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void {}

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

        return $this;
    }

    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Post $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->addLike($this);
        }

        return $this;
    }

    public function removeLike(Post $like): static
    {
        if ($this->likes->removeElement($like)) {
            $like->removeLike($this);
        }
        return $this;
    }

    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(self $follower): static
    {
        if (!$this->followers->contains($follower)) {
            $this->followers->add($follower);
        }

        return $this;
    }

    public function removeFollower(self $follower): static
    {
        $this->followers->removeElement($follower);

        return $this;
    }

    public function getFollowing(): Collection
    {
        return $this->following;
    }

    public function addFollowing(self $following): static
    {
        if (!$this->following->contains($following)) {
            $this->following->add($following);
            $following->addFollower($this);
        }

        return $this;
    }

    public function removeFollowing(self $following): static
    {
        if ($this->following->removeElement($following)) {
            $following->removeFollower($this);
        }

        return $this;
    }

    public function getStories(): Collection
    {
        return $this->stories;
    }

    public function addStory(Story $story): static
    {
        if (!$this->stories->contains($story)) {
            $this->stories->add($story);
            $story->setAuthor($this);
        }

        return $this;
    }

    public function removeStory(Story $story): static
    {
        if ($this->stories->removeElement($story)) {
            if ($story->getAuthor() === $this) {
                $story->setAuthor(null);
            }
        }

        return $this;
    }

    public function getStoriesLikes(): Collection
    {
        return $this->storiesLikes;
    }

    public function addStoriesLike(Story $storiesLike): static
    {
        if (!$this->storiesLikes->contains($storiesLike)) {
            $this->storiesLikes->add($storiesLike);
            $storiesLike->addLike($this);
        }

        return $this;
    }

    public function removeStoriesLike(Story $storiesLike): static
    {
        if ($this->storiesLikes->removeElement($storiesLike)) {
            $storiesLike->removeLike($this);
        }

        return $this;
    }
    
    /**
     * @return Collection<int, post>
     */
    public function getCompartidos(): Collection
    {
        return $this->compartidos;
    }

    public function addCompartido(post $compartido): static
    {
        if (!$this->compartidos->contains($compartido)) {
            $this->compartidos->add($compartido);
        }

        return $this;
    }

    public function removeCompartido(post $compartido): static
    {
        $this->compartidos->removeElement($compartido);

        return $this;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): static
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations->add($conversation);
            $conversation->setUser1($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        if ($this->conversations->removeElement($conversation)) {
            if ($conversation->getUser1() === $this) {
                $conversation->setUser1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMyMessages(): Collection
    {
        return $this->myMessages;
    }

    public function addMyMessage(Message $myMessage): static
    {
        if (!$this->myMessages->contains($myMessage)) {
            $this->myMessages->add($myMessage);
            $myMessage->setSender($this);
        }

        return $this;
    }

    public function removeMyMessage(Message $myMessage): static
    {
        if ($this->myMessages->removeElement($myMessage)) {
            if ($myMessage->getSender() === $this) {
                $myMessage->setSender(null);
            }
        }

        return $this;
    }
}
