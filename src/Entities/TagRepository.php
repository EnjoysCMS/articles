<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Entities;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class TagRepository extends EntityRepository
{
    public function like(string $field, string $value, int $limit = 20)
    {
        return $this->getLikeQuery($field, $value, $limit)->getResult();
    }

    public function getLikeQuery(string $field, string $value, int $limit = 20): Query
    {
        return $this->getLikeQueryBuilder($field, $value, $limit)->getQuery();
    }

    public function getLikeQueryBuilder(string $field, string $value, int $limit = 20): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->select('t')
            ->where("t.{$field} LIKE :value ")
            ->setParameter('value', $value . '%')
            ->setMaxResults($limit)
        ;
    }

    public function getUsedTags()
    {
        return $this->getUsedTagsQuery()->getResult();
    }

    public function getUsedTagsQuery(): Query
    {

        return $this->getUsedTagsQueryBuilder()->getQuery();
    }

    public function getUsedTagsQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->select('t')
            ->join('t.articles', 'a');
    }
}
