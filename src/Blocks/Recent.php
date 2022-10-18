<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Blocks;


use Doctrine\ORM\EntityManager;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Articles\Entities\ArticleRepository;
use EnjoysCMS\Core\Components\Blocks\AbstractBlock;
use EnjoysCMS\Core\Entities\Block as Entity;
use Twig\Environment;

final class Recent extends AbstractBlock
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
        /** @var ArticleRepository $repository */
        $repository = $this->em->getRepository(Article::class);
        $qb = $repository->getFindAllBuilder();
        $qb->setMaxResults($this->getOption('limit', 5));
        $qb->andWhere('a.status = true')
           ->andWhere('a.published <= :published')
           ->setParameter('published', new \DateTimeImmutable('now'));
        $articles = $qb->getQuery()->getResult();
        return $this->twig->render(
            (string)$this->getOption('template'),
            [
                'articles' => $articles,
                'options' => $this->getOptions()
            ]
        );
    }
}
