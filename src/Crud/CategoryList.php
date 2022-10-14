<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Crud;


use Doctrine\ORM\EntityManager;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Articles\Entities\Category;

final class CategoryList
{
    public function __construct(private EntityManager $em)
    {
    }

    public function __invoke()
    {
        return [
            'categories' => $this->em->getRepository(Category::class)->findAll()
        ];
    }
}
