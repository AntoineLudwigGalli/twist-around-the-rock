<?php

namespace App\Controller;

use App\Entity\CarrouselImages;
use App\Form\CarrouselImagesFormType;
use App\Form\ProductFormType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
class AdminPanelController extends AbstractController
{
    #[Route('/admin/panel', name: 'admin_panel')]
    public function admin_home(ManagerRegistry $doctrine, PaginatorInterface $paginator): Response
    {
        $em = $doctrine->getManager();

        $query = $em->createQuery('SELECT ci FROM App\Entity\CarrouselImages ci ');
        $carrouselImages = $paginator->paginate($query, 55);

        $query = $em->createQuery('SELECT a FROM App\Entity\Article a');
        $articles = $paginator->paginate($query, 55);

        return $this->render('admin_panel/home.html.twig', [
            'controller_name' => 'AdminPanelController',
            'carrouselImages' => $carrouselImages,
            'articles' => $articles

        ]);
    }

    #[Route('/admin/carrousel-images/liste', name: 'admin_carrousel_images_list')]
    public function admin_carrousel_images_list(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {
        // On récupère dans l'URL la donnée GET page (si elle n'existe pas, la valeur retournée par défaut sera la page 1)
        $requestedPage = $request->query->getInt('page', 1);

        // Si le numéro de page demandé dans l'URL est inférieur à 1, erreur 404
        if ($requestedPage < 1) {
            throw new NotFoundHttpException();
        }
        // Récupération du manager des entités
        $em = $doctrine->getManager();

        // Création d'une requête qui servira au paginator pour récupérer les images de la page courante
        $query = $em->createQuery('SELECT ci FROM App\Entity\CarrouselImages ci  ORDER BY ci.id ASC');

        //On stocke dans $carrouselImages les 10 images de la page demandée dans l'URL
        $carrouselImages = $paginator->paginate(
            $query, // Requête de selection des émissions en BDD
            $requestedPage, // Numéro de la page dont on veut les émissions
            10); // Nombre d'émissions par page

        return $this->render('admin_panel/carrousel-images-list.html.twig', [
            'controller_name' => 'AdminPanelController',
            'carrouselImages' => $carrouselImages,]);
    }

    #[Route('/admin/carrousel-images/ajouter-une-image', name: 'admin_add_carrousel_image')]
    public function createCarrouselImage(ManagerRegistry $doctrine, Request $request): Response
    {

        // Création d'une nouvelle instance de la classe CarrouselImage
        $newCarrouselImage = new CarrouselImages();

        $form = $this->createForm(CarrouselImagesFormType::class, $newCarrouselImage);

        // Symfony va remplir $newCarrouselImage grâce aux données du formulaire envoyé
        $form->handleRequest($request);

        $newCarrouselImage = $form->getData();

        if ($form->isSubmitted()) {

            if (!$form->isValid()) {
                    $errorMessage = "L'ajout de l'image a échoué, veuillez ré-essayer.";
                $this->addFlash("error", $errorMessage);
            }
            else {
                // récupération du manager des entités et sauvegarde en BDD de $newCarrouselImage
                $em = $doctrine->getManager();

//            Enregistrement de la photo avec nouveau nom
                $image = $form->get('image')->getData();

                /*Génération nom*/
                if($image) {
                    do {
                        $newFileName = md5(random_bytes(50)) . '.' . $image->guessExtension();
                    } while (file_exists($this->getParameter('app.carrousel.image.folder') . $newFileName));

                    $newCarrouselImage->setImage($newFileName);

                    $image->move(
                        $this->getParameter('app.carrousel.image.folder'),
                        $newFileName,
                    );
                }
                $em->persist($newCarrouselImage);

                $em->flush();

                // Ajout message flash
                $successMessage = "L'image a été ajoutée avec succès au carrousel d'accueil";
                $this->addFlash("success", $successMessage);
                return $this->redirectToRoute('admin_carrousel_images_list');
            }
        }

        // Pour que la vue puisse afficher le formulaire, on doit lui envoyer le formulaire généré, avec $form->createView()
        return $this->render('admin_panel/add-carrousel-images.html.twig', [
            'form' => $form->createView(),
            ]);
    }

    #[Route('/admin/carrousel-images/supprimer-une-image/{id}', name: 'admin_delete_carrousel_image', priority: 10)]
    public function carrouselImageDelete(CarrouselImages $carrouselImages, Request $request, ManagerRegistry $doctrine): Response
    {

//        Token
        $csrfToken = $request->query->get('csrf_token', '');

        if (!$this->isCsrfTokenValid('admin_delete_carrousel_image' . $carrouselImages->getId(), $csrfToken)) {

            $this->addFlash('error', 'Token de sécurité invalide, veuillez ré-essayer.');

        }
        else {
            // Suppression de l'image en BDD
            $em = $doctrine->getManager();
            $em->remove($carrouselImages);
            $em->flush();

            // Message flash de succès
            $this->addFlash('success', "L'image a été supprimé avec succès du carrousel d'accueil !");
        }
        // Redirection vers la page qui liste les events
        return $this->redirectToRoute('admin_carrousel_images_list');
    }

    /**
     *
     *
     */
    #[Route('/admin/carrousel-images/modifier-une-image/{id}', name: 'admin_edit_carrousel_image', priority: 10)]
    public function carrouselImageEdit(CarrouselImages $carrouselImages, Request $request, ManagerRegistry $doctrine): Response
    {


        $form = $this->createForm(CarrouselImagesFormType::class, $carrouselImages);

        //instanciation d\'un formulaire
        $form->handleRequest($request);

        //Verif du formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('image')->getData();



            if ($image == null){

                $em = $doctrine->getManager();
                $em->flush();

            } else {
                if(
                    $carrouselImages->getImage() != null &&
                    file_exists($this->getParameter('app.carrousel.image.folder') . $carrouselImages->getImage() )
                ){
                    unlink($this->getParameter('app.carrousel.image.folder') . $carrouselImages->getImage() );
                }
                /*Génération nom*/
                do {
                    $newFileName = md5(random_bytes(100)) . '.' . $image->guessExtension();
                } while (file_exists($this->getParameter('app.carrousel.image.folder') . $newFileName));

                $carrouselImages->setImage($newFileName);

                $em = $doctrine->getManager();
                $em->flush();

                $image->move(
                    $this->getParameter('app.carrousel.image.folder'),
                    $newFileName,
                );
            }
            // Message flash de succès
            $this->addFlash('success', 'Image modifiée avec succès !');

            // Redirection vers la liste des évènements contenant maintenant l'évènement modifié
            return $this->redirectToRoute('admin_carrousel_images_list', [
                'id' => $carrouselImages->getId(),
                ]);

        }

        return $this->render('admin_panel/edit_carrousel_images.html.twig', [
            'form' => $form->createView(),
            ]);
    }

    #[Route('/admin/blog/liste', name: 'admin_blog_articles_list')]
    public function admin_blog_articles_list(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {
        // On récupère dans l'URL la donnée GET page (si elle n'existe pas, la valeur retournée par défaut sera la page 1)
        $requestedPage = $request->query->getInt('page', 1);

        // Si le numéro de page demandé dans l'URL est inférieur à 1, erreur 404
        if ($requestedPage < 1) {
            throw new NotFoundHttpException();
        }
        // Récupération du manager des entités
        $em = $doctrine->getManager();

        // Création d'une requête qui servira au paginator pour récupérer les images de la page courante
        $query = $em->createQuery('SELECT a FROM App\Entity\Article a  ORDER BY a.publicationDate ASC');

        //On stocke dans $carrouselImages les 10 images de la page demandée dans l'URL
        $articles = $paginator->paginate(
            $query, // Requête de selection des émissions en BDD
            $requestedPage, // Numéro de la page dont on veut les émissions
            10); // Nombre d'émissions par page

        return $this->render('admin_panel/blog-articles-list.html.twig', [
            'controller_name' => 'AdminPanelController',
            'articles' => $articles,
        ]);
    }


//    Produits

    #[Route('/admin/produits/liste', name: 'admin_products_list')]
    public function admin_products_list(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {
        // On récupère dans l'URL la donnée GET page (si elle n'existe pas, la valeur retournée par défaut sera la page 1)
        $requestedPage = $request->query->getInt('page', 1);

        // Si le numéro de page demandé dans l'URL est inférieur à 1, erreur 404
        if ($requestedPage < 1) {
            throw new NotFoundHttpException();
        }
        // Récupération du manager des entités
        $em = $doctrine->getManager();

        // Création d'une requête qui servira au paginator pour récupérer les produits de la page courante
        $query = $em->createQuery('SELECT p FROM App\Entity\Product p  ORDER BY p.creationDate ASC');

        //On stocke dans $products les 10 images de la page demandée dans l'URL
        $products = $paginator->paginate(
            $query, // Requête de selection des produits en BDD
            $requestedPage, // Numéro de la page dont on veut les produits
            10); // Nombre de produits par page

        return $this->render('admin_panel/products-list.html.twig', [
            'controller_name' => 'AdminPanelController',
            'products' => $products,
        ]);
    }
}
