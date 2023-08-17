<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Crud;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\Mapping\MappingException;
use Enjoys\Forms\Form;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Articles\Entities\ArticleRepository;
use EnjoysCMS\Articles\Entities\Category;
use EnjoysCMS\Articles\Entities\CategoryRepository;
use Psr\Http\Message\ServerRequestInterface;

final class CategoryDelete
{

    private Category $category;

    /**
     * @throws NoResultException
     * @throws NotSupported
     */
    public function __construct(
        private readonly EntityManager $em,
        private readonly ServerRequestInterface $request
    ) {
        $this->category = $this->em->getRepository(Category::class)->find(
            $this->request->getAttribute('id', 0)
        ) ?? throw new NoResultException();
    }


    /**
     * @return Form
     */
    public function getForm(): Form
    {
        $form = new Form();
        $form->setDefaults([
            'set_parent_category' => [0]
        ]);
        $form->checkbox('remove_childs')->fill(['+ Удаление дочерних категорий']);
        $form->checkbox('set_parent_category')->setPrefixId('set_parent_category')->fill(
            [
                sprintf(
                    'Установить для статей из удаляемых категорий родительскую категорию (%s)',
                    $this->category->getParent()?->getTitle()
                )
            ]
        );
        $form->submit('delete', 'Удалить');
        return $form;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws MappingException
     */
    public function doAction(): void
    {
        /** @var CategoryRepository $repoCategory */
        $repoCategory = $this->em->getRepository(Category::class);
        /** @var ArticleRepository $repoArticles */
        $repoArticles = $this->em->getRepository(Article::class);

        $setCategory = (($this->request->getParsedBody(
            )['set_parent_category'] ?? null) !== null) ? $this->category->getParent() : null;

        if (($this->request->getParsedBody()['remove_childs'] ?? null) !== null) {
            $allCategoryIds = $repoCategory->getAllIds($this->category);
            $articles = $repoArticles->findByCategoriesIds($allCategoryIds);
            $this->setCategory($articles, $setCategory);
            $this->em->remove($this->category);
        } else {
            $articles = $repoArticles->findByCategory($this->category);
            $this->setCategory($articles, $setCategory);
            $repoCategory->removeFromTree($this->category);
            $repoCategory->updateLevelValues();
            $this->em->clear();
        }
        $this->em->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    private function setCategory($articles, ?Category $category = null): void
    {
        foreach ($articles as $article) {
            $article->setCategory($category);
        }
        $this->em->flush();
    }
}
