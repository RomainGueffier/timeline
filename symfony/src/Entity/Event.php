<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="array")
     */
    private $start = [];

    /**
     * @ORM\Column(type="array")
     */
    private $end = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $duration;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $source;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image_filename;

    /**
     * Many Events have Many Categories.
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="events")
     * @ORM\JoinTable(name="events_categories")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $categories;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Timeline", inversedBy="events")
     */
    private $timeline;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="events")
     */
    private $user;

    public function __construct() {
        $this->categories = new ArrayCollection();
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

    public function getStart(): ?array
    {
        return $this->start;
    }

    public function setStart(array $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?array
    {
        return $this->end;
    }

    public function setEnd(array $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSource(): ?string
    {
        return is_array($this->source) ? implode(',', $this->source) : '';
    }

    public function setSource(?string $source): self
    {
        $this->source = explode(',', $source);

        return $this;
    }

    public function getImageFilename(): ?string
    {
        return $this->image_filename;
    }

    public function setImageFilename(?string $image_filename): self
    {
        $this->image_filename = $image_filename;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

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
}
