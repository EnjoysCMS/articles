<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Crud;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\QueryException;
use Doctrine\Persistence\ObjectRepository;
use Enjoys\Forms\AttributeFactory;
use Enjoys\Forms\Form;
use Enjoys\Forms\Interfaces\RendererInterface;
use EnjoysCMS\Articles\Entities\Category;
use EnjoysCMS\Articles\Entities\CategoryRepository;
use EnjoysCMS\Core\Http\Response\RedirectInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function json_decode;

final class CategoryList
{


    private CategoryRepository|ObjectRepository|EntityRepository $repository;

    public function __construct(
        private EntityManager $em,
        private ServerRequestInterface $request,
        private UrlGeneratorInterface $urlGenerator,
        private RendererInterface $renderer,
        private RedirectInterface $redirect,
    ) {
        $this->repository = $this->em->getRepository(Category::class);
    }

    /**
     * @throws ORMException
     */
    private function _recursive($data, ?Category $parent = null): void
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

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws QueryException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function __invoke(): array
    {
        $form = new Form();
        $form->hidden('nestable-output')->setAttribute(AttributeFactory::create('id', 'nestable-output'));
        $form->submit('save', 'Сохранить');


        if ($form->isSubmitted()) {
            $this->_recursive(json_decode($this->request->getParsedBody()['nestable-output'] ?? []));
            $this->em->flush();
            $this->redirect->toRoute('articles/admin/category', emit: true);
        }
        $this->renderer->setForm($form);


        return [
            'form' => $this->renderer->output(),
            'categories' => $this->repository->getChildNodes(),
            'breadcrumbs' => [
                $this->urlGenerator->generate('@admin_index') => 'Главная',
                $this->urlGenerator->generate('articles/admin/list') => 'Статьи',
                'Категории',
            ],
        ];
    }
}
