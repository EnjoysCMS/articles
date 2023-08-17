<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Controllers;


use Doctrine\ORM\EntityManager;
use EnjoysCMS\Articles\Entities\Tag;
use EnjoysCMS\Articles\Entities\TagRepository;
use EnjoysCMS\Core\AbstractController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Annotation\Route;

final class ToolsControllers extends AbstractController
{
    #[Route(
        path: '/articles/tools/find-tag',
        name: 'articles/find-tag',
        options: [
            'acl' => false
        ],
        methods: ['get']
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
