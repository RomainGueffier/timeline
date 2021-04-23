<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CharacterRepository::class)
 * @ORM\Table(name="`character`")
 */
class Character
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $parent;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"export", "export_all"})
     */
    private $name;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"export", "export_all"})
     */
    private $birth;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"export", "export_all"})
     */
    private $birthplace;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"export", "export_all"})
     */
    private $death;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"export", "export_all"})
     */
    private $deathplace;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"export", "export_all"})
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"export", "export_all"})
     */
    private $age;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"export", "export_all"})
     */
    private $source;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"export", "export_all"})
     */
    private $weight;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageFilename;

    /**
     * Many Characters have Many Categories.
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="characters")
     * @ORM\JoinTable(name="characters_categories")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $categories;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Timeline", inversedBy="characters")
     */
    private $timeline;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="characters")
     */
    private $user;

    public function __construct() {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?int
    {
        return $this->parent;
    }

    public function setParent(?int $parent): self
    {
        $this->parent = $parent;

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

    public function getBirth(): ?array
    {
        return $this->birth;
    }

    public function setBirth(?array $birth): self
    {
        $this->birth = $birth;

        return $this;
    }

    public function getBirthplace(): ?string
    {
        return $this->birthplace;
    }

    public function setBirthplace(?string $birthplace): self
    {
        $this->birthplace = $birthplace;

        return $this;
    }

    public function getDeath(): ?array
    {
        return $this->death;
    }

    public function setDeath(?array $death): self
    {
        $this->death = $death;

        return $this;
    }

    public function getDeathplace(): ?string
    {
        return $this->deathplace;
    }

    public function setDeathplace(?string $deathplace): self
    {
        $this->deathplace = $deathplace;

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

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getSource(): ?array
    {
        return $this->source;
    }

    public function setSource(?array $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    public function setImageFilename(?string $imageFilename): self
    {
        $this->imageFilename = $imageFilename;

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
