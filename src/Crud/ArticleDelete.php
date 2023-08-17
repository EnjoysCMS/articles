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
use EnjoysCMS\Core\Components\Helpers\Redirect;
use EnjoysCMS\Core\Components\WYSIWYG\WYSIWYG;
use EnjoysCMS\Core\Components\WYSIWYG\WysiwygConfig;
use EnjoysCMS\Core\Http\Response\RedirectInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ArticleDelete
{

    private Article $article;

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
        $this->article = $this->em->getRepository(Article::class)->find(
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
        $form->submit('delete', 'Удалить');
        return $form;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    private function doSave()
    {
        $this->em->remove($this->article);
        $this->em->flush();

        $this->redirect->toUrl($this->urlGenerator->generate('articles/admin/list'), emit: true);
    }

}
