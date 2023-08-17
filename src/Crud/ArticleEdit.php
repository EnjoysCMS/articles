<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Crud;


use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Enjoys\Forms\Elements\Html;
use Enjoys\Forms\Elements\Text;
use Enjoys\Forms\Exception\ExceptionRule;
use Enjoys\Forms\Form;
use Enjoys\Forms\Rules;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Articles\Entities\Category;
use EnjoysCMS\Articles\Entities\Tag;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

final class ArticleEdit
{

    private Article $article;

    /**
     * @throws NoResultException
     * @throws NotSupported
     */
    public function __construct(
        private readonly EntityManager $em,
        private readonly ServerRequestInterface $request,
    ) {
        $this->article = $this->em->getRepository(Article::class)->find(
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
        $form->setDefaults([
            'title' => $this->article->getTitle(),
            'status' => [(int)$this->article->isStatus()],
            'category' => $this->article->getCategory()?->getId(),
            'slug' => $this->article->getSlug(fool: false),
            'subtitle' => $this->article->getSubTitle(),
            'annotation' => $this->article->getAnnotation(),
            'author' => $this->article->getAuthor(),
            'source' => $this->article->getSource(),
            'img' => $this->article->getMainImage(),
            'publish' => $this->article->getPublished()?->format('Y-m-d H:i:s'),
            'tags' => implode(', ', $this->article->getTags()->map(fn($tag) => $tag->getTitle())->toArray()),
            'body' => $this->article->getBody(),
        ]);

        $form->checkbox('status', null)
            ->addClass(
                'custom-switch custom-switch-off-danger custom-switch-on-success',
                Form::ATTRIBUTES_FILLABLE_BASE
            )
            ->fill([1 => 'Статус']);

        $form->select('category', 'Категория')
            ->addRule(Rules::REQUIRED)
            ->fill(
                ['0' => '_без категории_'] + $this->em->getRepository(
                    Category::class
                )->getFormFillArray()
            );
        $form->text('title', 'Название (заголовок)')->addRule(Rules::REQUIRED);
        $form->text('slug', 'Уникальное имя для url')
            ->addRule(Rules::REQUIRED)
            ->addRule(Rules::CALLBACK, '/ - нельзя использовать', function () {
                return !preg_match('/\//', $this->request->getParsedBody()['slug'] ?? '');
            })
            ->addRule(Rules::CALLBACK, 'Использовать нельзя, уже используется', function () {
                $article = $this->em->getRepository(Article::class)->getFindByUrlBuilder(
                    $this->request->getParsedBody()['slug'] ?? '',
                    $this->em->getRepository(Category::class)->find(
                        $this->request->getParsedBody()['category'] ?? 0
                    )
                )->getQuery()->getOneOrNullResult();

                if ($article === null) {
                    return true;
                }
                return $article->getId() === $this->article->getId();
            })
            ->setDescription('Используется в URL');
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
            );

        $form->datetimelocal('publish', 'Дата публикации');
        $form->text('tags', 'Теги');
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
        $category = $this->request->getParsedBody()['category'] ?? 0;
        $this->article->setCategory($this->em->getRepository(Category::class)->find($category));
        $this->article->setStatus((bool)($this->request->getParsedBody()['status'] ?? false));
        $this->article->setTitle(
            $this->request->getParsedBody()['title'] ?? throw new InvalidArgumentException('Not set title')
        );
        $this->article->setSlug(
            $this->request->getParsedBody()['slug'] ?? throw new InvalidArgumentException('Not set slug')
        );
        $this->article->setSubTitle(
            $this->request->getParsedBody()['subtitle'] ? $this->request->getParsedBody()['subtitle'] : null
        );
        $this->article->setAuthor(
            $this->request->getParsedBody()['author'] ? $this->request->getParsedBody()['author'] : null
        );
        $this->article->setSource(
            $this->request->getParsedBody()['source'] ? $this->request->getParsedBody()['source'] : null
        );
        $this->article->setAnnotation($this->request->getParsedBody()['annotation'] ?? '');
        $this->article->setBody(
            $this->request->getParsedBody()['body'] ?? throw new InvalidArgumentException('Not set body of article')
        );

        $this->article->setMainImage(
            $this->request->getParsedBody()['img'] ? $this->request->getParsedBody()['img'] : null
        );


        $publish = $this->request->getParsedBody()['publish'];
        $this->article->setPublished($publish ? new DateTimeImmutable($publish) : null);

        $tags = array_filter(
            array_unique(array_map('trim', explode(',', $this->request->getParsedBody()['tags']))),
            fn($item) => !empty($item)
        );

        $this->article->getTags()->clear();

        foreach ($tags as $tag) {
            if ($tag instanceof Tag) {
                $this->article->addTag($tag);
                continue;
            }
            /** @var Tag $tagEntity */
            $tagEntity = $this->em->getRepository(Tag::class)->findOneBy(['title' => $tag]) ?? new Tag();
            $tagEntity->setTitle($tag);
            $this->em->persist($tagEntity);
            $this->article->addTag($tagEntity);
        }

        $this->em->flush();

    }

}
