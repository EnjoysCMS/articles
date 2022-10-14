<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Entities;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

final class ArticleRepository extends EntityRepository
{
    public function findBySlug(string $slugs): ?Article
    {
        $slugs = explode('/', $slugs);
        $slug = array_pop($slugs);

        /** @var  CategoryRepository $categoryRepository */
        $categoryRepository = $this->getEntityManager()->getRepository(
            Category::class
        );


        $category = $categoryRepository->findByPath(implode("/", $slugs));

        $dql = $this->getFindByUrlBuilder($slug, $category);

        $dql->andWhere('a.status = true');

        return $dql->getQuery()->getOneOrNullResult();
    }

    public function getFindByUrlBuilder(string $slug, ?Category $category = null): QueryBuilder
    {
        $dql = $this->createQueryBuilder('a')
            ->select('a', 'c')
            ->leftJoin('a.category', 'c')
        ;
        if ($category === null) {
            $dql->where('a.category IS NULL');
        } else {
            $dql->where('a.category = :category')
                ->setParameter('category', $category)
            ;
        }
        $dql->andWhere('a.slug = :slug')
            ->setParameter('slug', $slug)
        ;

        return $dql;
    }
}
