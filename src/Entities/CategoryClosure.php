<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Tree\Entity\MappedSuperclass\AbstractClosure;

#[ORM\Table(name: 'atricles_categories_closure')]
#[ORM\Entity]
class CategoryClosure extends AbstractClosure
{

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(name: '`descendant`', referencedColumnName: '`id`', nullable: false, onDelete: 'CASCADE')]
    protected $descendant;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(name: '`ancestor`', referencedColumnName: '`id`', nullable: false, onDelete: 'CASCADE')]
    protected $ancestor;

}
