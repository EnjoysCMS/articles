<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Controllers;


use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use EnjoysCMS\Articles\Config;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Articles\Entities\ArticleRepository;
use EnjoysCMS\Articles\Entities\Category;
use EnjoysCMS\Articles\Entities\CategoryRepository;
use EnjoysCMS\Core\AbstractController;
use EnjoysCMS\Core\Exception\NotFoundException;
use EnjoysCMS\Core\Pagination\Pagination;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route(
    path: 'articles/{slug}@{page}',
    name: 'articles/category/view',
    requirements: [
        'slug' => '(\s*|[^@]+)',
        'page' => '\d+'
    ],
    options: [
        'comment' => 'Просмотр категорий в public'
    ],
    defaults: [
        'slug' => null,
        'page' => 1,
    ]
)]
final class ViewCategory extends AbstractController
{
    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws NotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function __invoke(
        EntityManager $em,
        Config $config
    ): ResponseInterface {
        $pagination = new Pagination(
            $this->request->getAttribute('page', 1),
            $config->get('perPageLimit', false)
        );

        /** @var CategoryRepository $categoryRepository */
        /** @var ArticleRepository $articleRepository */
        $articleRepository = $em->getRepository(Article::class);
        $categoryRepository = $em->getRepository(Category::class);


        /** @var Category $category */
        $category = $categoryRepository->findByPath($this->request->getAttribute('slug'));

        if ($category === null && !empty($this->request->getAttribute('slug'))) {
            throw new NotFoundException();
        }


        if ($config->get('showSubCategoryArticles', false)) {
            $qb = $articleRepository->getFindByCategoriesIdsDQL($categoryRepository->getAllIds($category));
        } else {
            $qb = $articleRepository->getQueryBuilderFindByCategory($category);
        }

        $qb->andWhere('a.status = true')
            ->andWhere('a.published <= :published')
            ->setParameter('published', new DateTimeImmutable('now'))
            ->orderBy('a.published', 'desc')
        ;

        $qb->setFirstResult($pagination->getOffset())->setMaxResults($pagination->getLimitItems());


        $paginator = new Paginator($qb);
        $pagination->setTotalItems($paginator->count());

//dd($paginator->getQuery());
        /** @var Article $article */
        return $this->response(
            $this->twig->render(
                '@m/articles/category.twig',
                [
                    '_title' => sprintf(
                        '%2$s [стр. %3$s] - %1$s',
                        $this->setting->get('sitename'),
                        $category?->getFullTitle(reverse: true) ?? 'Статьи',
                        $pagination->getCurrentPage()
                    ),
                    'category' => $category,
                    'pagination' => $pagination,
                    'articles' => $paginator->getIterator()
                ]
            )
        );
    }
}
