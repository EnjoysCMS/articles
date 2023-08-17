<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Crud;


use DI\Container;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Enjoys\Forms\Form;
use Enjoys\Forms\Interfaces\RendererInterface;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Articles\Entities\ArticleRepository;
use EnjoysCMS\Articles\Entities\Category;
use EnjoysCMS\Articles\Entities\CategoryRepository;
use EnjoysCMS\Core\Http\Response\RedirectInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CategoryDelete
{

    private Category $category;

    /**
     * @throws NoResultException
     */
    public function __construct(
        private EntityManager $em,
        private ServerRequestInterface $request,
        private RendererInterface $renderer,
        private UrlGeneratorInterface $urlGenerator,
        private RedirectInterface $redirect,
    ) {
        $this->category = $this->em->getRepository(Category::class)->find(
            $this->request->getAttribute('id', 0)
        ) ?? throw new NoResultException();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function __invoke(Container $container): array
    {
        $form = $this->getForm();

        if ($form->isSubmitted()) {
            $this->doSave();
        }

        $this->renderer->setForm($form);

        return [
            'form' => $this->renderer
        ];
    }

    /**
     * @return Form
     */
    protected function getForm(): Form
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
     */
    private function doSave()
    {
        /** @var CategoryRepository $repoCategory */
        $repoCategory = $this->em->getRepository(Category::class);
        /** @var ArticleRepository $repoArticles */
        $repoArticles = $this->em->getRepository(Article::class);

        $setCategory = (($this->request->getParsedBody()['set_parent_category'] ?? null) !== null) ? $this->category->getParent() : null;

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

        $this->redirect->toUrl($this->urlGenerator->generate('articles/admin/category'), emit: true);
    }
    private function setCategory($articles, ?Category $category = null): void
    {
        foreach ($articles as $article) {
            $article->setCategory($category);
        }
        $this->em->flush();
    }
}
