<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Blocks;


use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use EnjoysCMS\Articles\Entities\Tag;
use EnjoysCMS\Articles\Entities\TagRepository;
use EnjoysCMS\Core\Block\AbstractBlock;
use EnjoysCMS\Core\Block\Annotation\Block;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Block(name: 'Популярные теги (articles)', options: [
    'template' => [
        'value' => '../modules/articles/template/blocks/tags.twig',
        'name' => 'Путь до template',
        'description' => 'Обязательно',
    ],
    'limit' => [
        'value' => 20,
        'name' => 'Кол-во тегов',
        'description' => 'Какое кол-во тегов выводить в блок',
    ],
    'cacheTime' => [
        'value' => 0,
        'name' => 'Время кэширования в сек',
        'description' => '0 - не кэшировать',
    ]
])]
final class Tags extends AbstractBlock
{

    public function __construct(private readonly Environment $twig, private readonly EntityManager $em)
    {
    }


    /**
     * @throws SyntaxError
     * @throws NotSupported
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function view(): ?string
    {
        /** @var TagRepository $repository */
        $repository = $this->em->getRepository(Tag::class);

        $qb = $repository->getUsedTagsQueryBuilder();
        $qb
            ->addSelect('COUNT(t) as HIDDEN cnt')
            ->groupBy('t.id')
            ->andWhere('a.status = true')
            ->andWhere('a.published <= :published')
            ->setParameter('published', new DateTimeImmutable('now'))
            ->orderBy('cnt', 'desc');

        return $this->twig->render(
            (string)$this->getBlockOptions()->getValue('template'),
            [
                'tags' => $qb->getQuery()->getResult(),
                'options' => $this->getBlockOptions()
            ]
        );
    }
}
