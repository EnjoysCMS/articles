<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Crud;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Enjoys\Forms\Exception\ExceptionRule;
use Enjoys\Forms\Form;
use Enjoys\Forms\Rules;
use EnjoysCMS\Articles\Entities\Category;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

final class CategoryEdit
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
     * @throws ExceptionRule
     * @throws NotSupported
     */
    public function getForm(): Form
    {
        $form = new Form();
        $form->setDefaults(
            [
                'parent' => $this->category->getParent()?->getId(),
                'title' => $this->category->getTitle(),
                'slug' => $this->category->getSlug(fool: false),
                'description' => $this->category->getDescription()
            ]
        );


        $form->select('parent', 'Родительская категория')
            ->addRule(Rules::REQUIRED)
            ->fill(
                ['0' => '_без родительской категории_'] + $this->em->getRepository(
                    Category::class
                )->getFormFillArray()
            );

        $form->text('title', 'Название (заголовок)')->addRule(Rules::REQUIRED);
        $form->text('slug', 'Уникальное имя для url')
            ->addRule(Rules::REQUIRED)
            ->addRule(Rules::CALLBACK, '/ - нельзя использовать', function () {
                return !preg_match('/\//', $this->request->getParsedBody()['slug'] ?? '');
            })
            ->addRule(Rules::CALLBACK, 'Такое уже есть...нельзя)))', function () {
                $category = $this->em->getRepository(Category::class)->findOneBy(
                    [
                        'slug' => $this->request->getParsedBody()['slug'] ?? '',
                        'parent' => $this->category->getParent()
                    ]
                );

                if ($category === null) {
                    return true;
                }
                return $category->getId() === $this->category->getId();
            })
            ->setDescription('Используется в URL');
        $form->textarea('description', 'Описание');
        $form->submit('save', 'Сохранить');
        return $form;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws Exception
     */
    public function doAction(): void
    {
        /** @var Category|null $parent */
        $parent = $this->em->getRepository(Category::class)->find($this->request->getParsedBody()['parent'] ?? 0);

        $this->category->setParent($parent);
        $this->category->setStatus(true);
        $this->category->setTitle(
            $this->request->getParsedBody()['title'] ?? throw new InvalidArgumentException('Not set title')
        );
        $this->category->setSlug(
            $this->request->getParsedBody()['slug'] ?? throw new InvalidArgumentException('Not set slug')
        );

        $this->category->setDescription($this->request->getParsedBody()['description'] ?? '');
        $this->em->flush();
    }
}
