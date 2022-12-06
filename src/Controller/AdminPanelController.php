<?php

namespace App\Controller;

use App\Entity\CarrouselImages;
use App\Form\CarrouselImagesFormType;
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

        return $this->render('admin_panel/home.html.twig', [
            'controller_name' => 'AdminPanelController',
            'carrouselImages' => $carrouselImages

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
}
