<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Tree(type="closure")
 * @Gedmo\TreeClosure(class="EnjoysCMS\Articles\Entities\CategoryClosure")
 * @ORM\Entity(repositoryClass="CategoryRepository")
 * @ORM\Table(name="articles_categories")
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * This parameter is optional for the closure strategy
     *
     * @ORM\Column(name="level", type="integer", nullable=true)
     * @Gedmo\TreeLevel
     */
    private int $level;

    /**
     * @ORM\Column(name="sort", type="integer", options={"default": 0})
     */
    private int $sort;

    /**
     * @Gedmo\TreeParent
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private bool $status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="string")
     */
    private string $slug;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $description = null;


    public function getId()
    {
        return $this->id;
    }

    public function isStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }


    public function getSlug(): string
    {
        $parent = $this->getParent();
        if ($parent === null) {
            return $this->slug;
        }
        return $parent->getSlug() . '/' . $this->slug;
    }


    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function setParent(Category $parent = null): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Category|null
     */
    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function setLevel($level): void
    {
        $this->level = $level;
    }

    public function getLevel(): int
    {
        return $this->level;
    }


    public function getSort(): int
    {
        return $this->sort;
    }


    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    public function getChildren(): Collection
    {

        $iterator = $this->children->getIterator();

        /** @var Collection $c */
        $iterator->uasort(function ($first, $second){
            return $first->getSort() <=> $second->getSort();
        });

        return new ArrayCollection(iterator_to_array($iterator));
    }

}
