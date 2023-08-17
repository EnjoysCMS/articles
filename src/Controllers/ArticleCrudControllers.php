<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Controllers;


use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Enjoys\Forms\Exception\ExceptionRule;
use EnjoysCMS\Articles\Config;
use EnjoysCMS\Articles\Crud\ArticleAdd;
use EnjoysCMS\Articles\Crud\ArticleDelete;
use EnjoysCMS\Articles\Crud\ArticleEdit;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Core\ContentEditor\ContentEditor;
use EnjoysCMS\Core\Routing\Annotation\Route;
use EnjoysCMS\Module\Admin\AdminController;
use Psr\Http\Message\ResponseInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route('/articles/admin', '@articles_')]
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
     * @throws NotSupported
     */
    #[Route(
        name: 'list',
        comment: 'Список всех статей (обзор)'
    )]
    public function list(EntityManager $em): ResponseInterface
    {
        return $this->response(
            $this->twig->render(
                '@articles/crud/list.twig',
                [
                    'articles' => $em->getRepository(Article::class)->findAll()
                ]
            )
        );
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ExceptionRule
     */
    #[Route(
        path: '/add',
        name: 'add',
        comment: 'Добавить новую статью'
    )]
    public function add(
        ArticleAdd $add,
        Config $config,
        \EnjoysCMS\Module\Admin\Config $adminConfig,
        ContentEditor $contentEditor
    ): ResponseInterface {
        $form = $add->getForm();

        if ($form->isSubmitted()) {
            $add->doAction();
            return $this->redirect->toRoute('@articles_list');
        }

        $rendererForm = $adminConfig->getRendererForm($form);

        return $this->response(
            $this->twig->render(
                '@articles/crud/add.twig',
                [
                    'contentEditor' => [
                        $contentEditor->withConfig($config->getEditorConfig('annotation'))->setSelector(
                            '#annotation'
                        )->getEmbedCode(),
                        $contentEditor->withConfig($config->getEditorConfig('body'))->setSelector(
                            '#body'
                        )->getEmbedCode(),
                    ],
                    'form' => $rendererForm
                ]
            )
        );
    }

    /**
     * @throws DependencyException
     * @throws ExceptionRule
     * @throws LoaderError
     * @throws NotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RuntimeError
     * @throws SyntaxError
     */
    #[Route(
        path: '/edit@{id}',
        name: 'edit',
        requirements: [
            'id' => '\d+'
        ],
        comment: 'Редактировать статью'
    )]
    public function edit(
        ArticleEdit $edit,
        Config $config,
        \EnjoysCMS\Module\Admin\Config $adminConfig,
        ContentEditor $contentEditor,
    ): ResponseInterface {
        $form = $edit->getForm();

        if ($form->isSubmitted()) {
            $edit->doAction();
            return $this->redirect->toRoute('@articles_list');
        }

        $rendererForm = $adminConfig->getRendererForm($form);

        return $this->response(
            $this->twig->render(
                '@articles/crud/edit.twig',
                [
                    'contentEditor' => [
                        $contentEditor->withConfig($config->getEditorConfig('annotation'))->setSelector(
                            '#annotation'
                        )->getEmbedCode(),
                        $contentEditor->withConfig($config->getEditorConfig('body'))->setSelector(
                            '#body'
                        )->getEmbedCode(),
                    ],
                    'form' => $rendererForm
                ]
            )
        );
    }

    /**
     * @throws DependencyException
     * @throws LoaderError
     * @throws NotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RuntimeError
     * @throws SyntaxError
     */
    #[Route(
        path: '/delete@{id}',
        name: 'delete',
        requirements: [
            'id' => '\d+'
        ],
        comment: 'Удалить статью'
    )]
    public function delete(
        ArticleDelete $delete,
        \EnjoysCMS\Module\Admin\Config $adminConfig,
    ): ResponseInterface {
        $form = $delete->getForm();
        if ($form->isSubmitted()) {
            $delete->doAction();
            return $this->redirect->toRoute('@articles_list');
        }
        $rendererForm = $adminConfig->getRendererForm($form);
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
