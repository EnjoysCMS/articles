<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Crud;


use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Enjoys\Forms\AttributeFactory;
use Enjoys\Forms\Elements\Html;
use Enjoys\Forms\Elements\Text;
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

final class ArticleAdd
{
    public function __construct(
        private EntityManager $em,
        private ServerRequestInterface $request,
        private RendererInterface $renderer,
        private UrlGeneratorInterface $urlGenerator,
        private Config $config
    ) {
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
        $paramsWysiwyg = $this->config->getModuleConfig()->get('WYSIWYG');

        $_annotation = new WysiwygConfig($paramsWysiwyg['annotation'] ?? null);
        $wysiwygAnnotation = WYSIWYG::getInstance($_annotation->getEditorName(), $container);
        $wysiwygAnnotation?->getEditor()->setTwigTemplate($_annotation->getTemplate());

        $_body = new WysiwygConfig($paramsWysiwyg['body'] ?? null);
        $wysiwygBody = WYSIWYG::getInstance($_body->getEditorName(), $container);
        $wysiwygBody?->getEditor()->setTwigTemplate($_body->getTemplate());

        $form = $this->getForm();

        if ($form->isSubmitted()) {
            $this->doSave();
        }

        $this->renderer->setForm($form);
        return [
            'wysiwyg' => [
                $wysiwygAnnotation?->selector('#annotation'),
                $wysiwygBody?->selector('#body'),
            ],
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
        $form->setDefaults([
            'publish' => (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s')
        ]);
        $form->select('category', 'Категория')
            ->addRule(Rules::REQUIRED)
            ->fill(
                ['0' => '_без категории_'] + $this->em->getRepository(
                    Category::class
                )->getFormFillArray()
            )
        ;
        $form->text('title', 'Название (заголовок)')->addRule(Rules::REQUIRED);
        $form->text('slug', 'Уникальное имя для url')
            ->addRule(Rules::REQUIRED)
            ->addRule(Rules::CALLBACK, '/ - нельзя использовать', function (){
                return !preg_match('/\//', $this->request->getParsedBody()['slug'] ?? '');
            })
            ->addRule(Rules::CALLBACK, 'Использовать нельзя, уже используется', function () {
                return is_null(
                    $this->em->getRepository(Article::class)->getFindByUrlBuilder(
                        $this->request->getParsedBody()['slug'] ?? '',
                        $this->em->getRepository(Category::class)->find(
                            $this->request->getParsedBody()['category'] ?? 0
                        )
                    )->getQuery()->getOneOrNullResult()
                );
            })
            ->setDescription('Используется в URL')
        ;
        $form->text('subtitle', 'Подзаголовок');
        $form->text('author', 'Автор');
        $form->text('source', 'Источник');
        $form->textarea('annotation', 'Аннотация');
        $form->textarea('body', 'Статья')->addRule(Rules::REQUIRED);
        $form->group('Изображение')
            ->add(
                [
                    new Text('img'),
                    new Html(
                        <<<HTML
<a class="btn btn-default btn-outline btn-upload"  id="inputImage" title="Upload image file">
    <span class="fa fa-upload "></span>
</a>
HTML
                    ),
                ]
            )
        ;
        $form->datetimelocal('publish', 'Дата публикации');
        $form->text('tags', 'Теги')->addAttribute(AttributeFactory::create('data-role', 'tagsinput'));
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
        $category = $this->request->getParsedBody()['category'] ?? 0;
//        $this->cookie->set('__catalog__last_category_when_add_product', $categoryId);

        $article = new Article();

        $article->setCategory($this->em->getRepository(Category::class)->find($category));
        $article->setStatus(true);
        $article->setTitle(
            $this->request->getParsedBody()['title'] ?? throw new \InvalidArgumentException('Not set title')
        );
        $article->setSlug(
            $this->request->getParsedBody()['slug']
            ?? throw new \InvalidArgumentException('Not set slug')
        );
        $article->setSubTitle($this->request->getParsedBody()['subtitle'] ?? null);
        $article->setAuthor($this->request->getParsedBody()['author'] ? $this->request->getParsedBody()['author'] : null);
        $article->setSource($this->request->getParsedBody()['source'] ? $this->request->getParsedBody()['source'] : null);
        $article->setAnnotation($this->request->getParsedBody()['title'] ?? '');
        $article->setBody(
            $this->request->getParsedBody()['body'] ?? throw new \InvalidArgumentException('Not set body of article')
        );

        $article->setMainImage($this->request->getParsedBody()['img'] ? $this->request->getParsedBody()['img'] : null);


        $publish = $this->request->getParsedBody()['publish'];
        $article->setPublished($publish ? new \DateTimeImmutable($publish) : null);

        $tags = array_filter(
            array_unique(array_map('trim', explode(',', $this->request->getParsedBody()['tags']))),
            fn($item) => !empty($item)
        );

        foreach ($tags as $tag) {
            if ($tag instanceof Tag) {
                $article->addTag($tag);
                continue;
            }
            /** @var Tag $tagEntity */
            $tagEntity = $this->em->getRepository(Tag::class)->findOneBy(['title' => $tag]) ?? new Tag();
            $tagEntity->setTitle($tag);
            $this->em->persist($tagEntity);
            $article->addTag($tagEntity);
        }

        $this->em->persist($article);
        $this->em->flush();

        Redirect::http($this->urlGenerator->generate('articles/admin/list'));
    }
}
