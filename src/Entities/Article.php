<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="ArticleRepository")
 * @ORM\Table(name="articles_list")
 */
class Article
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime_immutable", name="created_at")
     */
    private \DateTimeImmutable $created;

    /**
     * @Gedmo\Timestampable(on="change", field={"title", "body"})
     * @ORM\Column(type="datetime_immutable", nullable=true, name="updated_at")
     */
    private ?\DateTimeImmutable $updated = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true, name="published_at")
     */
    private ?\DateTimeImmutable $published = null;

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
     * @ORM\Column(type="string", nullable=true, length=255)
     */
    private ?string $subTitle = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $source = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $author = null;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $annotation = null;

    /**
     * @ORM\Column(type="text")
     */
    private string $body;

    /**
     * @ORM\ManyToMany(targetEntity="Tag")
     */
    private Collection $tags;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     */
    private ?Category $category;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $views = 0;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCreated(): \DateTimeImmutable
    {
        return $this->created;
    }

    public function getUpdated(): ?\DateTimeImmutable
    {
        return $this->updated;
    }

    public function getPublished(): ?\DateTimeImmutable
    {
        return $this->published;
    }

    public function setPublished(?\DateTimeImmutable $published): void
    {
        $this->published = $published;
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

    public function getSubTitle(): ?string
    {
        return $this->subTitle;
    }

    public function setSubTitle(?string $subTitle): void
    {
        $this->subTitle = $subTitle;
    }

    public function getAnnotation(): ?string
    {
        return $this->annotation;
    }

    public function setAnnotation(?string $annotation): void
    {
        $this->annotation = $annotation;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): void
    {
        $this->views = $views;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }


    public function addTag(Tag $tag): void
    {
        if ($this->tags->contains($tag)) {
            return;
        }
        $this->tags->add($tag);
    }


    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }

    public function getSlug(string $lastPartSlug = null, bool $fool = true): string
    {
        if ($fool === false){
            return $this->slug;
        }

        $category = $this->getCategory();

        $slug = null;
        if ($category instanceof Category) {
            $slug = $category->getSlug() . '/';
        }

        return $slug . ($lastPartSlug ?? $this->slug);
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): void
    {
        $this->source = $source;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }
}
