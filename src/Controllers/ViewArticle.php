<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Controllers;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Articles\Entities\ArticleRepository;
use EnjoysCMS\Core\BaseController;
use EnjoysCMS\Core\Components\Helpers\Setting;
use EnjoysCMS\Core\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[Route(
    path: 'article/{slug}.html',
    name: 'article/view',
    requirements: ['slug' => '[^.]+'],
    options: [
        'comment' => 'Просмотр статей в public'
    ]
)]
final class ViewArticle extends BaseController
{
    /**
     * @throws NoResultException
     */
    public function __invoke(EntityManager $em, Environment $twig, ServerRequestInterface $request): ResponseInterface
    {
        /** @var ArticleRepository $articleRepository */
        $articleRepository = $em->getRepository(Article::class);
        $article = $articleRepository->findBySlug($request->getAttribute('slug')) ?? throw new NotFoundException();

        /** @var Article $article */
        return $this->responseText(
            $twig->render(
                '@m/articles/view.twig',
                [
                    '_title' => sprintf(
                        '%2$s - %3$s - %1$s',
                        Setting::get('sitename'),
                        $article->getTitle(),
                        $article->getCategory()?->getFullTitle(reverse: true) ?? 'Статьи'
                    ),
                    'article' => $article
                ]
            )
        );
    }
}
