<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Controllers;


use DI\Container;
use EnjoysCMS\Articles\Crud\Add;
use EnjoysCMS\Articles\Crud\ArticlesList;
use EnjoysCMS\Module\Admin\AdminBaseController;
use EnjoysCMS\Module\Pages\Admin\Index;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Annotation\Route;

final class CrudControllers extends AdminBaseController
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
                $this->container->call(Add::class)
            )
        );
    }
}
