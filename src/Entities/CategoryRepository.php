<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Entities;


use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Exception\InvalidArgumentException;
use Gedmo\Tree\Entity\Repository\ClosureTreeRepository;

class CategoryRepository extends ClosureTreeRepository
{

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function findByPath(?string $path)
    {
        if ($path === null){
            return null;
        }
        $slugs = explode('/', $path);
        $first = array_shift($slugs);
        $alias = 'c';
        $dql = $this->createQueryBuilder($alias);

        $parameters = ['slug' => $first];

        $dql->where("{$alias}.parent IS NULL AND {$alias}.slug = :slug  AND {$alias}.status = true");
        $parentJoin = "{$alias}.id";

        foreach ($slugs as $k => $slug) {
            $alias = $alias . $k;
            //
            $dql->leftJoin(
                \EnjoysCMS\Articles\Entities\Category::class,
                $alias,
                Expr\Join::WITH,
                "{$alias}.parent = $parentJoin AND {$alias}.slug = :slug{$k} AND {$alias}.status = true"
            );

            $parameters['slug' . $k] = $slug;

            $parentJoin = $alias . '.id';
        }
        //$dql->andWhere("{$alias}.status = true");
        $dql->select($alias);

        $dql->setParameters($parameters);

        $query = $dql->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @throws QueryException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getChildNodes(
        $node = null,
        array $criteria = [],
        string $orderBy = 'sort',
        string $direction = 'asc'
    ) {
        return $this
            ->getChildNodesQuery($node, $criteria, $orderBy, $direction)
//            ->setFetchMode(Category::class, 'children', ClassMetadata::FETCH_EAGER)
            ->getResult()
        ;
    }

    /**
     * @throws QueryException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getChildNodesQuery(
        $node = null,
        array $criteria = [],
        string $orderBy = 'sort',
        string $direction = 'asc'
    ): Query {
        return $this->getChildNodesQueryBuilder($node, $criteria, $orderBy, $direction)->getQuery();
    }

    /**
     * @throws QueryException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getChildNodesQueryBuilder(
        $node = null,
        array $criteria = [],
        string $orderBy = 'sort',
        string $direction = 'asc'
    ): QueryBuilder {
        $currentLevel = 0;

        $maxLevel = $this->createQueryBuilder('c')
            ->select('max(c.level)')
            ->getQuery()
            ->getSingleScalarResult();

        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $dql = $this->getQueryBuilder();
        if ($node === null) {
            $dql->select('node')
                ->from($config['useObjectClass'], 'node')
                ->where('node.' . $config['parent'] . ' IS NULL')
            ;
        } else {
            $currentLevel = $this->createQueryBuilder('c')
                ->select('c.level')
                ->where('c.id = :node')
                ->setParameter('node', $node)
                ->getQuery()
                ->getSingleScalarResult();

            $dql->select('node')
                ->from($config['useObjectClass'], 'node')
                ->where('node.' . $config['parent'] . ' = :node')
                ->setParameter('node', $node)
            ;
        }

        if ($meta->hasField($orderBy) && in_array(strtolower($direction), ['asc', 'desc'])) {
            $dql->orderBy('node.' . $orderBy, $direction);
        } else {
            throw new InvalidArgumentException(
                "Invalid sort options specified: field - {$orderBy}, direction - {$direction}"
            );
        }


        foreach ($criteria as $field => $value) {
            $dql->addCriteria(Criteria::create()->where(Criteria::expr()->eq($field, $value)));
        }

        $parentAlias = 'node';
        for ($i = $currentLevel + 2; $i <= $maxLevel + 1; $i++) {
            $condition = "c{$i}.level = $i and c{$i}.parent = {$parentAlias}.id";
            foreach ($criteria as $field => $value) {
                $condition .= " AND c{$i}.{$field} = :{$field}";
                // параметры биндятся автоматически, чудеса )
                // $parameters[$field] = $value;
            }

            $dql->addOrderBy("c{$i}.{$orderBy}", $direction);

            $dql->leftJoin(
                "{$parentAlias}.children",
                "c{$i}",
                Expr\Join::WITH,
                $condition
            );

            $parentAlias = "c{$i}";
            $dql->addSelect("c{$i}");
        }

        return $dql;
    }

    /**
     * @throws QueryException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getFormFillArray(): array
    {
        return $this->_build($this->getChildNodes());
    }

    private function _build($tree, $level = 1): array
    {
        $ret = [];

        foreach ($tree as $item) {
            $ret[$item->getId()] = str_repeat("&nbsp;", ($level - 1) * 3) . $item->getTitle();
            if (count($item->getChildren()) > 0) {
                $ret += $this->_build($item->getChildren(), $item->getLevel() + 1);
            }
        }
        return $ret;
    }

    public function getAllIds($node = null): array
    {
        $nodes = $this->getChildren($node);
        $ids = array_map(
            function ($node) {
                return $node?->getId();
            },
            $nodes
        );
        $ids[] = $node?->getId();
        return $ids;
    }


}
