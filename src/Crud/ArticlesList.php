<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Crud;


use Doctrine\ORM\EntityManager;
use EnjoysCMS\Articles\Entities\Article;

final class ArticlesList
{
    public function __construct(private EntityManager $em)
    {
    }

    public function __invoke()
    {
        return [
            'articles' => $this->em->getRepository(Article::class)->findAll()
        ];
    }
}
