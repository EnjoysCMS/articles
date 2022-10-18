<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Blocks;


use Doctrine\ORM\EntityManager;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Articles\Entities\ArticleRepository;
use EnjoysCMS\Articles\Entities\CategoryRepository;
use EnjoysCMS\Articles\Entities\Tag;
use EnjoysCMS\Articles\Entities\TagRepository;
use EnjoysCMS\Core\Components\Blocks\AbstractBlock;
use EnjoysCMS\Core\Entities\Block as Entity;
use Twig\Environment;

final class Tags extends AbstractBlock
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
        /** @var TagRepository $repository */
        $repository = $this->em->getRepository(Tag::class);
        // todo: need improved query result
        $result = $repository->findBy([], limit: $this->getOption('limit', 20));
        return $this->twig->render(
            (string)$this->getOption('template'),
            [
                'tags' => $result,
                'options' => $this->getOptions()
            ]
        );
    }
}
