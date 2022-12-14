<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Controllers;


use DI\Container;
use EnjoysCMS\Articles\Crud\ArticleAdd;
use EnjoysCMS\Articles\Crud\ArticleDelete;
use EnjoysCMS\Articles\Crud\ArticleEdit;
use EnjoysCMS\Articles\Crud\ArticlesList;
use EnjoysCMS\Module\Admin\AdminBaseController;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Annotation\Route;

final class ArticleCrudControllers extends AdminBaseController
{

    public function __construct(private Container $container, ResponseInterface $response = null)
    {
        parent::__construct($this->container, $response);
        $this->getTwig()->getLoader()->addPath(__DIR__ . '/../../template', 'articles');
    }
    #[Route(
        path: '/articles/admin',
        name: 'articles/admin/list',
        options: [
            'comment' => '[Admin] Список всех статей (обзор)'
        ]
    )]
    public function list(): ResponseInterface
    {
        return $this->responseText(
            $this->getTwig()->render(
                '@articles/crud/list.twig',
                $this->container->call(ArticlesList::class)
            )
        );
    }

    #[Route(
        path: '/articles/admin/add',
        name: 'articles/admin/add',
        options: [
            'comment' => '[Admin] Добавить новую статью'
        ]
    )]
    public function add(): ResponseInterface
    {
        return $this->responseText(
            $this->getTwig()->render(
                '@articles/crud/add.twig',
                $this->container->call(ArticleAdd::class)
            )
        );
    }

    #[Route(
        path: '/articles/admin/edit@{id}',
        name: 'articles/admin/edit',
        requirements: [
            'id' => '\d+'
        ],
        options: [
            'comment' => '[Admin] Редактировать статью'
        ]
    )]
    public function edit(): ResponseInterface
    {
        return $this->responseText(
            $this->getTwig()->render(
                '@articles/crud/edit.twig',
                $this->container->call(ArticleEdit::class)
            )
        );
    }

    #[Route(
        path: '/articles/admin/delete@{id}',
        name: 'articles/admin/delete',
        requirements: [
            'id' => '\d+'
        ],
        options: [
            'comment' => '[Admin] Удалить статью'
        ]
    )]
    public function delete(): ResponseInterface
    {
        return $this->responseText(
            $this->getTwig()->render(
                '@articles/crud/remove.twig',
                $this->container->call(ArticleDelete::class)
            )
        );
    }
}
