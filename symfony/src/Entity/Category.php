<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"export", "export_all"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"export", "export_all"})
     */
    private $description;

    /**
     * Many Categories have Many Characters.
     * @ORM\ManyToMany(targetEntity="Character", mappedBy="categories")
     * @ORM\JoinTable(name="characters_categories")
     * @ORM\OrderBy({"name" = "ASC"})
     * @Groups({"export_all"})
     */
    private $characters;

    /**
     * Many Categories have Many Events.
     * @ORM\ManyToMany(targetEntity="Event", mappedBy="categories")
     * @ORM\JoinTable(name="events_categories")
     * @ORM\OrderBy({"name" = "ASC"})
     * @Groups({"export_all"})
     */
    private $events;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Timeline", inversedBy="categories")
     */
    private $timeline;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="categories")
     */
    private $user;

    public function __construct() {
        $this->characters = new ArrayCollection();
        $this->events = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Character[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Character $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->addCategory($this);
        }

        return $this;
    }

    public function removeCategory(Character $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            $category->removeCategory($this);
        }

        return $this;
    }

    /**
     * @return Collection|Character[]
     */
    public function getCharacters(): Collection
    {
        return $this->characters;
    }

    public function addCharacter(Character $character): self
    {
        if (!$this->characters->contains($character)) {
            $this->characters[] = $character;
            $character->addCategory($this);
        }

        return $this;
    }

    public function removeCharacter(Character $character): self
    {
        if ($this->characters->removeElement($character)) {
            $character->removeCategory($this);
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->addCategory($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            $event->removeCategory($this);
        }

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

    public function getTimeline(): ?Timeline
    {
        return $this->timeline;
    }

    public function setTimeline(?Timeline $timeline): self
    {
        $this->timeline = $timeline;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->getTimeline()->getVisibility();
    }

    /**
    * Remove all children for importation
    */
    public function removeAllChildren(): self
    {
        $this->events->clear();
        $this->characters->clear();

        return $this;
    }
}
