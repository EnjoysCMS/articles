<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Blocks;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\QueryException;
use EnjoysCMS\Articles\Entities\CategoryRepository;
use EnjoysCMS\Core\Block\AbstractBlock;
use EnjoysCMS\Core\Block\Annotation\Block;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Block(name: 'Категории (articles)', options: [
    'template' => [
        'value' => '../modules/articles/template/blocks/category.twig',
        'name' => 'Путь до template',
        'description' => 'Обязательно',
    ],
    'cacheTime' => [
        'value' => 0,
        'name' => 'Время кэширования в сек',
        'description' => '0 - не кэшировать',
    ]
])]
final class Category extends AbstractBlock
{

    public function __construct(private readonly Environment $twig, private readonly EntityManager $em)
    {
    }

    /**
     * @throws RuntimeError
     * @throws LoaderError
     * @throws SyntaxError
     * @throws QueryException
     * @throws NonUniqueResultException
     * @throws NotSupported
     * @throws NoResultException
     */
    public function view(): ?string
    {
        /** @var CategoryRepository $repository */
        $repository = $this->em->getRepository(\EnjoysCMS\Articles\Entities\Category::class);
        return $this->twig->render(
            (string)$this->getBlockOptions()->getValue('template'),
            [
                'categories' => $repository->getChildNodes(),
                'options' => $this->getBlockOptions()
            ]
        );
    }
}
