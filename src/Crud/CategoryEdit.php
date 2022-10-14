<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Crud;


use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Enjoys\Forms\Exception\ExceptionRule;
use Enjoys\Forms\Form;
use Enjoys\Forms\Interfaces\RendererInterface;
use Enjoys\Forms\Rules;
use EnjoysCMS\Articles\Config;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Articles\Entities\Category;
use EnjoysCMS\Articles\Entities\Tag;
use EnjoysCMS\Core\Components\Helpers\Redirect;
use EnjoysCMS\Core\Components\WYSIWYG\WYSIWYG;
use EnjoysCMS\Core\Components\WYSIWYG\WysiwygConfig;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class CategoryEdit
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
        private Config $config
    ) {
        $this->category = $this->em->getRepository(Category::class)->find(
            $this->request->getAttribute('id', 0)
        ) ?? throw new NoResultException();
    }

    /**
     * @throws ORMException
     * @throws RuntimeError
     * @throws LoaderError
     * @throws DependencyException
     * @throws OptimisticLockException
     * @throws SyntaxError
     * @throws NotFoundException
     * @throws ExceptionRule
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
     * @throws ExceptionRule
     */
    protected function getForm(): Form
    {
        $form = new Form();
        $form->setDefaults(
            [
                'parent' => $this->category->getParent()?->getId(),
                'title' => $this->category->getTitle(),
                'slug' => $this->category->getSlug(),
                'description' => $this->category->getDescription()
            ]
        );


        $form->select('parent', 'Родительская категория')
            ->fill(
                ['0' => '_без родительской категории_'] + $this->em->getRepository(
                    Category::class
                )->getFormFillArray()
            )
            ->addRule(Rules::REQUIRED)
        ;

        $form->text('title', 'Название (заголовок)')->addRule(Rules::REQUIRED);
        $form->text('slug', 'Уникальное имя для url')->addRule(Rules::REQUIRED)->setDescription('Используется в URL');
        $form->textarea('description', 'Описание');
        $form->submit('save', 'Сохранить');
        return $form;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws \Exception
     */
    private function doSave()
    {
        /** @var Category|null $parent */
        $parent = $this->em->getRepository(Category::class)->find($this->request->getParsedBody()['parent'] ?? 0);
        $category = new Category();
        $category->setParent($parent);
        $category->setStatus(true);
        $category->setSort(0);
        $category->setTitle(
            $this->request->getParsedBody()['title'] ?? throw new \InvalidArgumentException('Not set title')
        );
        $category->setSlug(
            $this->request->getParsedBody()['slug'] ?? throw new \InvalidArgumentException('Not set slug')
        );

        $category->setDescription($this->request->getParsedBody()['description'] ?? '');

        $this->em->persist($category);
        $this->em->flush();

        Redirect::http($this->urlGenerator->generate('articles/admin/category'));
    }
}
