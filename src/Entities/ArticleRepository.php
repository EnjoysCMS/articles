<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Entities;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

final class ArticleRepository extends EntityRepository
{
    public function getFindAllBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'c')
            ->leftJoin('a.category', 'c');
    }

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
        $dql->andWhere('a.published <= :published');
        $dql->setParameter('published', new \DateTimeImmutable('now'));

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

    public function findByCategory(Category $category)
    {
        return $this->getQueryFindByCategory($category)->getResult();
    }

    public function getQueryFindByCategory(?Category $category): Query
    {

        return $this->getQueryBuilderFindByCategory($category)->getQuery();
    }

    public function getQueryBuilderFindByCategory(?Category $category): QueryBuilder
    {

        if ($category === null) {
            return $this->getFindAllBuilder()->where('a.category IS NULL');
        }

        return $this->getFindAllBuilder()
            ->where('a.category = :category')
            ->setParameter('category', $category);
    }

    public function getFindByCategoriesIdsDQL($categoryIds): QueryBuilder
    {
        $qb = $this->getFindAllBuilder();

        $qb->where('a.category IN (:category)')
            ->setParameter('category', $categoryIds);

        if (false !== $null_key = array_search(null, $categoryIds)) {
            $qb->orWhere('a.category IS NULL');
        }

        return $qb;
    }

    public function getFindByCategoriesIdsQuery($categoryIds): Query
    {
        return $this->getFindByCategoriesIdsDQL($categoryIds)->getQuery();
    }

    public function findByCategoriesIds($categoryIds)
    {
        return $this->getFindByCategoriesIdsQuery($categoryIds)->getResult();
    }

    public function findByTag(Tag $tag)
    {
        return $this->getQueryFindByTag($tag)->getResult();
    }

    public function getQueryFindByTag(Tag $tag): Query
    {

        return $this->getQueryBuilderFindByTag($tag)->getQuery();
    }

    public function getQueryBuilderFindByTag(Tag $tag): QueryBuilder
    {
        return $this->getFindAllBuilder()
            ->leftJoin('a.tags', 't')
            ->andWhere('t = :tag')
            ->setParameter('tag', $tag);
    }
}
