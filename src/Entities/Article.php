<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Entities;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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
     * @ORM\Column(type="datetime_immutable", name="created_at")
     */
    private \DateTimeImmutable $created;

    /**
     * @ORM\Column(type="datetime_immutable", name="updated_at")
     */
    private \DateTimeImmutable $updated;

    /**
     * @ORM\Column(type="datetime_immutable", name="published_at")
     */
    private \DateTimeImmutable $published;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private bool $status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     */
    private ?string $subTitle = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $annotation = null;

    /**
     * @ORM\Column(type="text")
     */
    private string $body;

    private Collection $tags;

    private Collection $categories;

    /**
     * @ORM\Column(type="integer", options={"default": true})
     */
    private int $views = 0;

    public function getId()
    {
        return $this->id;
    }

    public function getCreated(): \DateTimeImmutable
    {
        return $this->created;
    }

    public function setCreated(\DateTimeImmutable $created): void
    {
        $this->created = $created;
    }

    public function getUpdated(): \DateTimeImmutable
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeImmutable $updated): void
    {
        $this->updated = $updated;
    }

    public function getPublished(): \DateTimeImmutable
    {
        return $this->published;
    }

    public function setPublished(\DateTimeImmutable $published): void
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
}
