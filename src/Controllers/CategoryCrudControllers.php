<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Controllers;


use DI\Container;
use EnjoysCMS\Articles\Crud\ArticleAdd;
use EnjoysCMS\Articles\Crud\ArticleDelete;
use EnjoysCMS\Articles\Crud\ArticleEdit;
use EnjoysCMS\Articles\Crud\ArticlesList;
use EnjoysCMS\Articles\Crud\CategoryAdd;
use EnjoysCMS\Articles\Crud\CategoryEdit;
use EnjoysCMS\Articles\Crud\CategoryList;
use EnjoysCMS\Module\Admin\AdminBaseController;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Annotation\Route;

final class CategoryCrudControllers extends AdminBaseController
{

    public function __construct(private Container $container, ResponseInterface $response = null)
    {
        parent::__construct($this->container, $response);
        $this->getTwig()->getLoader()->addPath(__DIR__ . '/../../template', 'articles');
    }
    #[Route(
        path: '/articles/admin/category',
        name: 'articles/admin/category',
        options: [
            'comment' => '[Admin] Список категорий'
        ]
    )]
    public function list(): ResponseInterface
    {
        return $this->responseText(
            $this->getTwig()->render(
                '@articles/crud/category_list.twig',
                $this->container->call(CategoryList::class)
            )
        );
    }

    #[Route(
        path: '/articles/admin/category/add',
        name: 'articles/admin/category/add',
        options: [
            'comment' => '[Admin] Добавить новую категорию'
        ]
    )]
    public function add(): ResponseInterface
    {
        return $this->responseText(
            $this->getTwig()->render(
                '@articles/crud/add.twig',
                $this->container->call(CategoryAdd::class)
            )
        );
    }

    #[Route(
        path: '/articles/admin/category/edit@{id}',
        name: 'articles/admin/category/edit',
        requirements: [
            'id' => '\d+'
        ],
        options: [
            'comment' => '[Admin] Редактировать категорию'
        ]
    )]
    public function edit(): ResponseInterface
    {
        return $this->responseText(
            $this->getTwig()->render(
                '@articles/crud/edit.twig',
                $this->container->call(CategoryEdit::class)
            )
        );
    }

    #[Route(
        path: '/articles/admin/category/delete@{id}',
        name: 'articles/admin/category/delete',
        requirements: [
            'id' => '\d+'
        ],
        options: [
            'comment' => '[Admin] Удалить категорию'
        ]
    )]
    public function delete(): ResponseInterface
    {
        return $this->responseText(
            $this->getTwig()->render(
                '@articles/crud/remove.twig',
                $this->container->call(CategoryDelete::class)
            )
        );
    }
}
