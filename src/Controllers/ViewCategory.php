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
use EnjoysCMS\Module\Pages\Entities\Page;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[Route(
    path: 'articles/{slug}',
    name: 'articles/category/view',
    requirements: ['slug' => '[^.]+'],
    options: [
        'comment' => 'Просмотр категорий в public'
    ]
)]
final class ViewCategory extends BaseController
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
//                    '_title' => sprintf(
//                        '%2$s - %1$s',
//                        Setting::get('sitename'),
//                        $page->getTitle()
//                    ),
                    'article' => $article
                ]
            )
        );
    }
}
