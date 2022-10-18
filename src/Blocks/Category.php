<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Blocks;


use Doctrine\ORM\EntityManager;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Articles\Entities\ArticleRepository;
use EnjoysCMS\Articles\Entities\CategoryRepository;
use EnjoysCMS\Core\Components\Blocks\AbstractBlock;
use EnjoysCMS\Core\Entities\Block as Entity;
use Twig\Environment;

final class Category extends AbstractBlock
{

    public function __construct(private Environment $twig, private EntityManager $em, Entity $block)
    {
        parent::__construct($block);
    }

    public static function getBlockDefinitionFile(): string
    {
        return __DIR__.'/../../blocks.yml';
    }

    public function view()
    {
        /** @var CategoryRepository $repository */
        $repository = $this->em->getRepository(\EnjoysCMS\Articles\Entities\Category::class);
        return $this->twig->render(
            (string)$this->getOption('template'),
            [
                'categories' => $repository->getChildNodes(),
                'options' => $this->getOptions()
            ]
        );
    }
}
