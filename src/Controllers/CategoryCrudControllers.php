<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Controllers;


use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\QueryException;
use Doctrine\Persistence\Mapping\MappingException;
use Enjoys\Forms\AttributeFactory;
use Enjoys\Forms\Exception\ExceptionRule;
use Enjoys\Forms\Form;
use EnjoysCMS\Articles\Crud\CategoryAdd;
use EnjoysCMS\Articles\Crud\CategoryDelete;
use EnjoysCMS\Articles\Crud\CategoryEdit;
use EnjoysCMS\Articles\Crud\CategoryList;
use EnjoysCMS\Core\Routing\Annotation\Route;
use EnjoysCMS\Module\Admin\AdminController;
use EnjoysCMS\Module\Admin\Config as AdminConfig;
use Psr\Http\Message\ResponseInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route('/articles/admin/category', '@articles_category_')]
final class CategoryCrudControllers extends AdminController
{

    public function __construct(
        Container $container,
        private readonly AdminConfig $adminConfig
    ) {
        parent::__construct($container);
        $this->twig->getLoader()->addPath(__DIR__ . '/../../template', 'articles');
    }

    /**
     * @throws LoaderError
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws QueryException
     */
    #[Route(
        name: 'list',
        comment: 'Список категорий'
    )]
    public function list(
        CategoryList $categoryList,
        EntityManager $em,
        AdminConfig $adminConfig,
    ): ResponseInterface {
        $this->breadcrumbs->add('@articles_list', 'Статьи')->setLastBreadcrumb('Категории');

        $form = new Form();
        $form->hidden('nestable-output')->setAttribute(AttributeFactory::create('id', 'nestable-output'));
        $form->submit('save', 'Сохранить');


        if ($form->isSubmitted()) {
            $categoryList->_recursive(json_decode($this->request->getParsedBody()['nestable-output'] ?? []));
            $em->flush();
            $this->redirect->toRoute('@articles_category_list', emit: true);
        }

        $rendererForm = $adminConfig->getRendererForm($form);

        return $this->response(
            $this->twig->render(
                '@articles/crud/category_list.twig',
                [
                    'form' => $rendererForm->output(),
                    'categories' => $categoryList->getRepository()->getChildNodes()
                ]
            )
        );
    }

    /**
     * @throws ExceptionRule
     * @throws ORMException
     * @throws RuntimeError
     * @throws LoaderError
     * @throws DependencyException
     * @throws OptimisticLockException
     * @throws SyntaxError
     * @throws NotFoundException
     */
    #[Route(
        path: '/add',
        name: 'add',
        comment: 'Добавить новую категорию'
    )]
    public function add(
        CategoryAdd $add
    ): ResponseInterface {
        $form = $add->getForm();

        if ($form->isSubmitted()) {
            $add->doAction();
            return $this->redirect->toRoute('@articles_category_list');
        }

        $rendererForm = $this->adminConfig->getRendererForm($form);

        return $this->response(
            $this->twig->render(
                '@articles/crud/add.twig',
                [
                    'form' => $rendererForm
                ]
            )
        );
    }

    /**
     * @throws ExceptionRule
     * @throws ORMException
     * @throws RuntimeError
     * @throws LoaderError
     * @throws DependencyException
     * @throws OptimisticLockException
     * @throws SyntaxError
     * @throws NotFoundException
     */
    #[Route(
        path: '/edit@{id}',
        name: 'edit',
        requirements: [
            'id' => '\d+'
        ],
        comment: 'Редактировать категорию'
    )]
    public function edit(CategoryEdit $edit): ResponseInterface
    {
        $form = $edit->getForm();

        if ($form->isSubmitted()) {
            $edit->doAction();
            return $this->redirect->toRoute('@articles_category_list');
        }

        $rendererForm = $this->adminConfig->getRendererForm($form);
        return $this->response(
            $this->twig->render(
                '@articles/crud/edit.twig',
                [
                    'form' => $rendererForm
                ]
            )
        );
    }

    /**
     * @throws ORMException
     * @throws MappingException
     * @throws RuntimeError
     * @throws DependencyException
     * @throws LoaderError
     * @throws OptimisticLockException
     * @throws SyntaxError
     * @throws NotFoundException
     */
    #[Route(
        path: '/delete@{id}',
        name: 'delete',
        requirements: [
            'id' => '\d+'
        ],
        comment: 'Удалить категорию'
    )]
    public function delete(CategoryDelete $delete): ResponseInterface
    {
        $form = $delete->getForm();

        if ($form->isSubmitted()) {
            $delete->doAction();
            return $this->redirect->toRoute('@articles_category_list');
        }

        $rendererForm = $this->adminConfig->getRendererForm($form);

        return $this->response(
            $this->twig->render(
                '@articles/crud/remove.twig',
                [
                    'form' => $rendererForm
                ]
            )
        );
    }
}
