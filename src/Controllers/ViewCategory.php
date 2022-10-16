<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Controllers;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use EnjoysCMS\Articles\Config;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Articles\Entities\ArticleRepository;
use EnjoysCMS\Articles\Entities\Category;
use EnjoysCMS\Articles\Entities\CategoryRepository;
use EnjoysCMS\Core\BaseController;
use EnjoysCMS\Core\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route(
    path: 'articles/{slug?}',
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
     * @throws NonUniqueResultException
     * @throws NotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \Exception
     */
    public function __invoke(EntityManager $em, Environment $twig, ServerRequestInterface $request, Config $config): ResponseInterface
    {
        /** @var CategoryRepository $categoryRepository */
        /** @var ArticleRepository $articleRepository */
        $articleRepository = $em->getRepository(Article::class);
        $categoryRepository = $em->getRepository(Category::class);

        $category = $categoryRepository->findByPath($request->getAttribute('slug'));

        if ($config->getModuleConfig()->get('showSubCategoryArticles', false)) {
            $qb = $articleRepository->getFindByCategoriesIdsDQL($categoryRepository->getAllIds($category));
        } else {
            $qb = $articleRepository->getQueryBuilderFindByCategory($category);
        }

        $qb->andWhere('a.status = true')
            ->andWhere('a.published <= :published')
            ->setParameter('published', new \DateTimeImmutable('now'))
            ->orderBy('a.published', 'desc');

        $paginator = new Paginator($qb);
//dd($paginator->getQuery());
        /** @var Article $article */
        return $this->responseText(
            $twig->render(
                    '@m/articles/category.twig',
                [
//                    '_title' => sprintf(
//                        '%2$s - %1$s',
//                        Setting::get('sitename'),
//                        $page->getTitle()
//                    ),
                    'articles' => $paginator->getIterator()
                ]
            )
        );
    }
}
