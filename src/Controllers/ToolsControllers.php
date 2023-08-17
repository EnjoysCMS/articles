<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Controllers;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use EnjoysCMS\Articles\Entities\Tag;
use EnjoysCMS\Articles\Entities\TagRepository;
use EnjoysCMS\Core\AbstractController;
use EnjoysCMS\Core\Routing\Annotation\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Route('/articles/tools', 'articles_tools_')]
final class ToolsControllers extends AbstractController
{
    /**
     * @throws NotSupported
     */
    #[Route(
        path: '/find-tag',
        name: 'find_tag',
        methods: ['get'],
        needAuthorized: false
    )]
    public function findTag(EntityManager $em, ServerRequestInterface $request): ResponseInterface
    {
        $searchValue = $request->getQueryParams()['search-value'] ?? '';
        if (mb_strlen($searchValue) < 3) {
            return $this->json([]);
        }
        /** @var TagRepository $tagRepository */
        $tagRepository = $em->getRepository(Tag::class);
        $result = $tagRepository->like('title', $searchValue, 3);

        return $this->json(
            array_map(function ($tag) {
                return $tag->getTitle();
            }, $result)
        );
    }
}
