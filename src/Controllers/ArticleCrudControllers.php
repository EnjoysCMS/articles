<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Controllers;


use DI\Container;
use EnjoysCMS\Articles\Crud\ArticleAdd;
use EnjoysCMS\Articles\Crud\ArticleDelete;
use EnjoysCMS\Articles\Crud\ArticleEdit;
use EnjoysCMS\Articles\Crud\ArticlesList;
use EnjoysCMS\Module\Admin\AdminController;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class ArticleCrudControllers extends AdminController
{

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->twig->getLoader()->addPath(__DIR__ . '/../../template', 'articles');
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(
        path: '/articles/admin',
        name: 'articles/admin/list',
        options: [
            'comment' => '[Admin] Список всех статей (обзор)'
        ]
    )]
    public function list(): ResponseInterface
    {
        return $this->response(
            $this->twig->render(
                '@articles/crud/list.twig',
                $this->container->call(ArticlesList::class)
            )
        );
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route(
        path: '/articles/admin/add',
        name: 'articles/admin/add',
        options: [
            'comment' => '[Admin] Добавить новую статью'
        ]
    )]
    public function add(): ResponseInterface
    {
        return $this->response(
            $this->twig->render(
                '@articles/crud/add.twig',
                $this->container->call(ArticleAdd::class)
            )
        );
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
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
        return $this->response(
            $this->twig->render(
                '@articles/crud/edit.twig',
                $this->container->call(ArticleEdit::class)
            )
        );
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
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
        return $this->response(
            $this->twig->render(
                '@articles/crud/remove.twig',
                $this->container->call(ArticleDelete::class)
            )
        );
    }
}
