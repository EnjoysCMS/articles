<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Blocks;


use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Articles\Entities\ArticleRepository;
use EnjoysCMS\Core\Block\AbstractBlock;
use EnjoysCMS\Core\Block\Annotation\Block;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Block(name: 'Недавние статьи (articles)', options: [
    'template' => [
        'value' => '../modules/articles/template/blocks/recent.twig',
        'name' => 'Путь до template',
        'description' => 'Обязательно',
    ],
    'limit' => [
        'value' => 5,
        'name' => 'Кол-во записей (статей)',
        'description' => 'Какое кол-во статей выводить в блок',
    ],
    'cacheTime' => [
        'value' => 0,
        'name' => 'Время кэширования в сек',
        'description' => '0 - не кэшировать',
    ]
])]
final class Recent extends AbstractBlock
{

    public function __construct(private readonly Environment $twig, private readonly EntityManager $em)
    {

    }


    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws NotSupported
     * @throws LoaderError
     */
    public function view(): ?string
    {
        /** @var ArticleRepository $repository */
        $repository = $this->em->getRepository(Article::class);
        $qb = $repository->getFindAllBuilder();
        $qb->setMaxResults($this->getBlockOptions()->getValue('limit') ?? 5);
        $qb->andWhere('a.status = true')
           ->andWhere('a.published <= :published')
           ->setParameter('published', new DateTimeImmutable('now'));
        $articles = $qb->getQuery()->getResult();
        return $this->twig->render(
            (string)$this->getBlockOptions()->getValue('template'),
            [
                'articles' => $articles,
                'options' => $this->getBlockOptions()
            ]
        );
    }
}
