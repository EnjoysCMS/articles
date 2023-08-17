<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Controllers;


use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Tools\Pagination\Paginator;
use EnjoysCMS\Articles\Config;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Articles\Entities\ArticleRepository;
use EnjoysCMS\Articles\Entities\Tag;
use EnjoysCMS\Articles\Entities\TagRepository;
use EnjoysCMS\Core\AbstractController;
use EnjoysCMS\Core\Exception\NotFoundException;
use EnjoysCMS\Core\Pagination\Pagination;
use EnjoysCMS\Core\Routing\Annotation\Route;
use Psr\Http\Message\ResponseInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route(
    path: 'articles/tag:{tag}@{page}',
    name: 'articles_tag_view',
    requirements: [
        'tag' => '([^@]+)',
        'page' => '\d+'
    ],
    defaults: [
        'page' => 1,
    ],
    priority: 1,
    comment: 'Просмотр тегов'
)]
final class ViewTag extends AbstractController
{
    /**
     * @throws LoaderError
     * @throws NotFoundException
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws NotSupported
     */
    public function __invoke(
        EntityManager $em,
        Config $config
    ): ResponseInterface {
        $pagination = new Pagination(
            $this->request->getAttribute('page', 1),
            $config->get('perPageLimit', false)
        );

        /** @var TagRepository $categoryRepository */
        /** @var ArticleRepository $articleRepository */
        $articleRepository = $em->getRepository(Article::class);
        $tagRepository = $em->getRepository(Tag::class);

        /** @var Tag $tag */
        $tag = $tagRepository->findOneBy([
            'title' => $this->request->getAttribute('tag')
        ]);


        $qb = $articleRepository->getQueryBuilderFindByTag($tag);

        $qb->andWhere('a.status = true')
            ->andWhere('a.published <= :published')
            ->setParameter('published', new DateTimeImmutable('now'))
            ->orderBy('a.published', 'desc');

        $qb->setFirstResult($pagination->getOffset())->setMaxResults($pagination->getLimitItems());

        $paginator = new Paginator($qb);
        $pagination->setTotalItems($paginator->count());

        /** @var Article $article */
        return $this->response(
            $this->twig->render(
                '@m/articles/tag.twig',
                [
                    '_title' => sprintf(
                        '%2$s - фильтр по тегу [стр. %3$s] - Статьи - %1$s',
                        $this->setting->get('sitename'),
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
