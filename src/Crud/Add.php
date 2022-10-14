<?php

declare(strict_types=1);


namespace EnjoysCMS\Articles\Crud;


use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Enjoys\Forms\Form;
use Enjoys\Forms\Interfaces\RendererInterface;
use EnjoysCMS\Articles\Config;
use EnjoysCMS\Articles\Entities\Article;
use EnjoysCMS\Core\Components\Helpers\Redirect;
use EnjoysCMS\Core\Components\WYSIWYG\WYSIWYG;
use EnjoysCMS\Core\Components\WYSIWYG\WysiwygConfig;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class Add
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
     */
    protected function getForm(): Form
    {
        $form = new Form();
        $form->text('title', 'Название (заголовок)');
        $form->text('slug', 'Уникальное имя для url')->setDescription('Используется в URL');
        $form->text('subtitle', 'Подзаголовок');
        $form->textarea('annotation', 'Аннотация');
        $form->textarea('body', 'Статья');
        $form->submit('save', 'Сохранить');
        return $form;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    private function doSave()
    {
        $article = new Article();
        $article->setStatus(true);
        $article->setTitle(
            $this->request->getParsedBody()['title'] ?? throw new \InvalidArgumentException('Not set title')
        );
        $article->setSlug(
            $this->request->getParsedBody()['slug'] ?? throw new \InvalidArgumentException('Not set slug')
        );
        $article->setSubTitle($this->request->getParsedBody()['subtitle'] ?? null);
        $article->setAnnotation($this->request->getParsedBody()['title'] ?? '');
        $article->setBody(
            $this->request->getParsedBody()['body'] ?? throw new \InvalidArgumentException('Not set body of article')
        );

        $this->em->persist($article);
        $this->em->flush();

        Redirect::http($this->urlGenerator->generate('articles/admin/list'));
    }
}
