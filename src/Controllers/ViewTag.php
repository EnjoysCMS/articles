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
use EnjoysCMS\Articles\Entities\Tag;
use EnjoysCMS\Articles\Entities\TagRepository;
use EnjoysCMS\Core\BaseController;
use EnjoysCMS\Core\Components\Helpers\Setting;
use EnjoysCMS\Core\Components\Pagination\Pagination;
use EnjoysCMS\Core\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route(
    path: 'articles/tag:{tag}@{page}',
    name: 'articles/tag/view',
    requirements: [
        'tag' => '([^@]+)',
        'page' => '\d+'
    ],
    options: [
        'comment' => 'Просмотр тегов в public'
    ],
    defaults: [
        'page' => 1,
    ],
    priority: 1
)]
final class ViewTag extends BaseController
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
    public function __invoke(
        EntityManager $em,
        Environment $twig,
        ServerRequestInterface $request,
        Config $config
    ): ResponseInterface {
        $pagination = new Pagination(
            $request->getAttribute('page', 1),
            $config->getModuleConfig()->get('perPageLimit', false)
        );

        /** @var TagRepository $categoryRepository */
        /** @var ArticleRepository $articleRepository */
        $articleRepository = $em->getRepository(Article::class);
        $tagRepository = $em->getRepository(Tag::class);

        /** @var Tag $tag */
        $tag = $tagRepository->findOneBy([
            'title' => $request->getAttribute('tag')
        ]);


        $qb = $articleRepository->getQueryBuilderFindByTag($tag);

        $qb->andWhere('a.status = true')
            ->andWhere('a.published <= :published')
            ->setParameter('published', new \DateTimeImmutable('now'))
            ->orderBy('a.published', 'desc')
        ;

        $qb->setFirstResult($pagination->getOffset())->setMaxResults($pagination->getLimitItems());

        $paginator = new Paginator($qb);
        $pagination->setTotalItems($paginator->count());

        /** @var Article $article */
        return $this->responseText(
            $twig->render(
                '@m/articles/tag.twig',
                [
                    '_title' => sprintf(
                        '%2$s - фильтр по тегу [стр. %3$s] - Статьи - %1$s',
                        Setting::get('sitename'),
                        $tag->getTitle(),
                        $pagination->getCurrentPage()
                    ),
                    'tag' => $tag,
                    'pagination' => $pagination,
                    'articles' => $paginator->getIterator()
                ]
            )
        );
    }
}
