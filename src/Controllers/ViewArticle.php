<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Controllers;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Articles\Entities\ArticleRepository;
use EnjoysCMS\Core\AbstractController;
use EnjoysCMS\Core\Exception\NotFoundException;
use EnjoysCMS\Core\Routing\Annotation\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route(
    path: 'article/{slug}.html',
    name: 'articles_view',
    requirements: ['slug' => '[^.]+'],
    comment: 'Просмотр статей'
)]
final class ViewArticle extends AbstractController
{
    /**
     * @throws LoaderError
     * @throws NotFoundException
     * @throws NotSupported
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function __invoke(EntityManager $em, ServerRequestInterface $request): ResponseInterface
    {
        /** @var ArticleRepository $articleRepository */
        $articleRepository = $em->getRepository(Article::class);
        $article = $articleRepository->findBySlug($request->getAttribute('slug')) ?? throw new NotFoundException();

        /** @var Article $article */
        return $this->response(
            $this->twig->render(
                '@m/articles/view.twig',
                [
                    '_title' => sprintf(
                        '%2$s - %3$s - %1$s',
                        $this->setting->get('sitename'),
                        $article->getTitle(),
                        $article->getCategory()?->getFullTitle(reverse: true) ?? 'Статьи'
                    ),
                    'article' => $article
                ]
            )
        );
    }
}
