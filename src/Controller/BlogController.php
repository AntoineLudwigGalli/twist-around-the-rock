<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleCarrouselImage;
use App\Form\ArticleCarrouselImageFormType;
use App\Form\ArticleFormType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/blog', name: 'blog_')]
class BlogController extends AbstractController {
    /**
     * Contrôleur de la page permettant de créer un nouvel article
     * Accès réservé aux administrateurs (ROLE_ADMIN)
     */
    #[Route('/nouvelle-publication/', name: 'new_publication')]
    #[isGranted('ROLE_ADMIN')]
    public function newPublication(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response {
        $article = new Article();

        $form = $this->createForm(ArticleFormType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setPublicationDate(new \DateTime())->setSlug($slugger->slug($article->getTitle())->lower());

            $image = $form->get('image')->getData();

            /*Génération nom*/
            if ($image) {
                do {
                    $newFileName = md5(random_bytes(50)) . '.' . $image->guessExtension();
                } while (file_exists($this->getParameter('app.article.image.folder') . $newFileName));

                $article->setImage($newFileName);

                $image->move(
                    $this->getParameter('app.article.image.folder'),
                    $newFileName,
                );

                $em = $doctrine->getManager();
                $em->persist($article);
                $em->flush();

                $this->addFlash('success', 'Article publié avec succès');

                return $this->redirectToRoute('blog_publication_view', [
                    'id' => $article->getId(),
                    'slug' => $article->getSlug(),]);
            }
        }

            return $this->render('blog/new_publication.html.twig', [
                'form' => $form->createView(),


            ]);
        }

    /**
     * Contrôleur de la page permettant de voir un article en détail (via ID et slug dans l'URL)
     */
    #[Route('/{id}/{slug}/', name: 'publication_view')]
    #[ParamConverter('article', options: ['mapping' => ['id' => 'id', 'slug' => 'slug']])]
    public function publicationView(Article $article, Request $request, ManagerRegistry $doctrine): Response {

        $articleCarrouselImagesRepo = $doctrine->getRepository(ArticleCarrouselImage::class);
        $articleCarrouselImages = $articleCarrouselImagesRepo->findBy(['article' => $article->getId()]);

        return $this->render('blog/publication_view.html.twig', [
            'article' => $article,
            'articleCarrouselImages' => $articleCarrouselImages,
        ]);
    }

    /**
     *
     */
    #[Route('/', name: 'publication_list')]
    public function publicationList(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response {

        $requestedPage = $request->query->getInt('page', 1);

        if ($requestedPage < 1) {
            throw new NotFoundHttpException();
        }

        $em = $doctrine->getManager();

        $query = $em->createQuery('SELECT a FROM App\Entity\Article a ORDER BY a.publicationDate DESC');
        $articles = $paginator->paginate($query, //Requête créée juste avant
            $requestedPage, // Page qu'on souhaite voir
            8, // Nombre d'articles à afficher par page
        );

        return $this->render('blog/publication_list.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * Contrôleur de la page admin via son id dans l'url
     *
     * Accès réservé aux admins
     */
    #[Route("/suppression/{id}/", name: 'publication_delete', priority: 10)]
    #[isGranted("ROLE_ADMIN")]
    public function publicationDelete(Article $article, Request $request, ManagerRegistry $doctrine): Response {
        $csrfToken = $request->query->get('csrf_token', '');

        if (!$this->isCsrfTokenValid('blog_publication_delete_' . $article->getId(), $csrfToken)) {
            $this->addFlash('error', 'Token de sécurité invalide, veuillez ré-essayer');
        } else {

            $em = $doctrine->getManager();
            $em->remove($article);
            $em->flush();

            $this->addFlash('success', 'Article supprimé avec succès');
        }

        return $this->redirectToRoute('blog_publication_list');
    }

    /**
     * Contrôleur de la page admin via son id dans l'url pour modifier un article
     *
     * Accès réservé aux admins
     */
    #[Route("/modifier/{id}/", name: 'publication_edit', priority: 10)]
    #[isGranted("ROLE_ADMIN")]
    public function publicationEdit(Article $article, Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response {

        $form = $this->createForm(ArticleFormType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $article->setSlug($slugger->slug($article->getTitle())->lower());

            $image = $form->get('image')->getData();

            if ($image == null){

                $em = $doctrine->getManager();
                $em->flush();

            } else {

                if (
                    $article->getImage() != null &&
                    file_exists($this->getParameter('app.article.image.folder') . $article->getImage())
                ) {
                    unlink($this->getParameter('app.article.image.folder') . $article->getImage());
                }


                /*Génération nom*/
                do {
                    $newFileName = md5(random_bytes(100)) . '.' . $image->guessExtension();
                } while (file_exists($this->getParameter('app.article.image.folder') . $newFileName));

                $article->setImage($newFileName);

                $em = $doctrine->getManager();
                $em->flush();

                $image->move(
                    $this->getParameter('app.article.image.folder'),
                    $newFileName,
                );
            }
            $this->addFlash('success', 'Article modifié avec succès');
            return $this->redirectToRoute('blog_publication_view', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),]);
        }

        return $this->render('blog/publication_edit.html.twig', ['form' => $form->createView(),]);
    }

    /**
     * Contrôleur de la page affichant les résultats des recherches faites par le formulaire de recherche dans la navbar
     */
    #[Route('/recherche/', name: 'search')]
    public function search(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {

        // Récupération du numéro de la page demandée dans l'url (si il existe pas, 1 sera pris à la place)
        $requestedPage = $request->query->getInt('page', 1);

        // Si la page demandée est inférieur à 1, erreur 404
        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        // On récupère la recherche de l'utilisateur depuis l'url ($_GET['q'])
        $search = $request->query->get('s', '');

        // Récupération du manager général des entités
        $em = $doctrine->getManager();

        // Création d'une requête permettant de récupérer les articles pour la page actuelle, dont le titre ou le contenu contient la recherche de l'utilisateur
        $query = $em
            ->createQuery('SELECT a FROM App\Entity\Article a WHERE a.title LIKE :search OR a.content LIKE :search ORDER BY a.publicationDate DESC')
            ->setParameters([
                'search' => '%' . $search . '%',
            ])
        ;

        // Récupération des articles
        $articles = $paginator->paginate(
            $query,
            $requestedPage,
            10
        );

        // Appel de la vue en lui envoyant les articles à afficher
        return $this->render('blog/list_search.html.twig', [
            'articles' => $articles,
        ]);
    }



    #[Route('{id}/ajouter-photo-carrousel', name: 'add_carousel_image', requirements: ["id" => "\d+"] )]
    #[isGranted('ROLE_ADMIN')]
    public function addArticleCarouselImage(Request $request, ManagerRegistry $doctrine, Article $article): \Symfony\Component\HttpFoundation\Response
    {
        $articleCarouselImage = new ArticleCarrouselImage();
        $form=$this->createForm(ArticleCarrouselImageFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $articleCarouselImage->setName($form->get('name')->getData());
            $articleCarouselImage->setArticle($article);
            $image = $form->get('image')->getData();

            if(
                $articleCarouselImage->getImage() != null &&
                file_exists($this->getParameter('app.article.carrousel.image.folder') . $articleCarouselImage->getImage() )
            ){
                unlink($this->getParameter('app.article.carrousel.image.folder') . $articleCarouselImage->getImage() );
            }


            /*Génération nom*/
            do{
                $newFileName = md5( random_bytes(100) ) . '.' . $image->guessExtension();
            } while (file_exists($this->getParameter('app.article.carrousel.image.folder') .$newFileName));

            $articleCarouselImage->setImage($newFileName);

            $em = $doctrine->getManager();
            $em->persist($articleCarouselImage);
            $em->flush();

            $image -> move(
                $this->getParameter('app.article.carrousel.image.folder'),
                $newFileName,
            );

            $this->addFlash('success', 'Photo du carrousel ajoutée avec succès');

            return $this->redirectToRoute('blog_publication_view', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
            ]);
        }

        return $this->render('blog/add_article_carousel_image.html.twig', [
            'form' => $form->createView(),
            'id' => $article->getId()
        ]);
    }

    #[Route('{articleId}/editer-photo-carrousel/{id}', name: 'edit_carousel_image')]
    #[isGranted('ROLE_ADMIN')]
    public function editArticleCarouselPicture(Request $request, ManagerRegistry $doctrine, ArticleCarrouselImage $articleCarrouselImage): \Symfony\Component\HttpFoundation\Response
    {

        $form = $this->createForm(ArticleCarrouselImageFormType::class);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

            if (
                $articleCarrouselImage->getImage() != null &&
                file_exists($this->getParameter('app.article.carrousel.image.folder') . $articleCarrouselImage->getImage())
            ) {
                unlink($this->getParameter('app.article.carrousel.image.folder') . $articleCarrouselImage->getImage());
            }


            /*Génération nom*/
            do {
                $newFileName = md5(random_bytes(100)) . '.' . $image->guessExtension();
            } while (file_exists($this->getParameter('app.article.carrousel.image.folder') . $newFileName));

            $articleCarrouselImage->setImage($newFileName);

            $em = $doctrine->getManager();
            $em->flush();

            $image->move(
                $this->getParameter('app.article.carrousel.image.folder'),
                $newFileName,
            );

            $this->addFlash('success', 'Photo du carrousel modifiée avec succès');

            return $this->redirectToRoute('blog_publication_view', [
                'id' => $articleCarrouselImage->getArticle()->getId(),
                'slug' => $articleCarrouselImage->getArticle()->getSlug(),
            ]);
        }

        return $this->render('blog/edit_article_carrousel_image.html.twig', [
            'form' => $form->createView(),
            'article_carousel_image' => $articleCarrouselImage,
            'articleId' => $articleCarrouselImage->getArticle(),
        ]);
    }

    #[Route('/supprimer-photo-carrousel/{id}', name: 'delete_carousel_image', priority: 10)]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteArticleCarouselPicture(ArticleCarrouselImage $articleCarrouselImage, Request $request, ManagerRegistry $doctrine): \Symfony\Component\HttpFoundation\RedirectResponse
    {
//        Token CSRF
        $csrfToken = $request->query->get('csrf_token', '');

        if (!$this->isCsrfTokenValid('delete_article_carousel_image' . $articleCarrouselImage->getId(), $csrfToken)) {

            $this->addFlash('error','Token de sécurité invalide, veuillez ré-essayer.');
        }
        else {
            unlink($this->getParameter('app.article.carrousel.image.folder') . $articleCarrouselImage->getImage());

            // Suppression de l'image en BDD
            $em = $doctrine->getManager();
            $em->remove($articleCarrouselImage);
            $em->flush();

            // Message flash de succès
            $this->addFlash('success', "La photo a été supprimée avec succès !");
        }
        // Redirection vers la page qui liste les events
        return $this->redirectToRoute('blog_publication_view', [
            'product_carrousel_image' => $articleCarrouselImage,
            'id' => $articleCarrouselImage->getArticle()->getId(),
            'slug' => $articleCarrouselImage->getArticle()->getSlug(),
        ]);
    }

}