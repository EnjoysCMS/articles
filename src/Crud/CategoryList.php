<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Crud;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ObjectRepository;
use EnjoysCMS\Articles\Entities\Category;
use EnjoysCMS\Articles\Entities\CategoryRepository;

final class CategoryList
{


    private CategoryRepository|ObjectRepository|EntityRepository $repository;

    /**
     * @throws NotSupported
     */
    public function __construct(
        private readonly EntityManager $em,
    ) {
        $this->repository = $this->em->getRepository(Category::class);
    }

    /**
     * @throws ORMException
     */
    public function _recursive($data, ?Category $parent = null): void
    {
        foreach ($data as $key => $value) {
            /** @var Category $item */
            $item = $this->repository->find($value->id);
            $item->setParent($parent);
            $item->setSort($key);
            $this->em->persist($item);
            if (isset($value->children)) {
                $this->_recursive($value->children, $item);
            }
        }
    }

    public function getRepository(): EntityRepository|CategoryRepository|ObjectRepository
    {
        return $this->repository;
    }


}
