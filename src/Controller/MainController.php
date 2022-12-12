<?php

namespace App\Controller;

use App\Entity\About;
use App\Entity\AboutCarrouselImage;
use App\Entity\CarrouselImages;
use App\Entity\DynamicContent;
use App\Form\AboutCarrouselImageFormType;
use App\Form\AboutFormType;
use App\Form\DynamicContentFormType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
#[Route("/", name: "main_")]
class MainController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ManagerRegistry $doctrine, PaginatorInterface $paginator, Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $carrouselImagesRepo = $doctrine->getRepository(CarrouselImages::class);
        $carrouselImages = $carrouselImagesRepo->findAll();

// Affichage du dernier article de blog
        $requestedPage = $request->query->getInt('page', 1);

        $em = $doctrine->getManager();

        $query = $em->createQuery('SELECT a FROM App\Entity\Article a ORDER BY a.publicationDate DESC');
        $articles = $paginator->paginate($query, //Requête créée juste avant
            $requestedPage, // Page qu'on souhaite voir
            1, // Nombre d'articles à afficher par page
        );

        return $this->render('main/home.html.twig', [
            'carrouselImages' => $carrouselImages,
            'articles' => $articles,

        ]);
    }

    #[Route('/contenu-dynamique/modifier/{title}', name: 'dynamic_content_edit', requirements: ["title" => "[a-z0-9_-]{2,50}"])]
    #[IsGranted('ROLE_ADMIN')]
    public function dynamicContentEdit(ManagerRegistry $doctrine, Request $request, $title): \Symfony\Component\HttpFoundation\Response
    {
        //On va chercher par nom (qui sert de clé) le dynamic content correspondant
        $dynamicContentRepo = $doctrine->getRepository(DynamicContent::class);

        $currentDynamicContent = $dynamicContentRepo->findOneByTitle($title);

        $em = $doctrine->getManager();

        // Si le contenu est vide, on en crée un avec le nom passé dans la fonction twig
        if (empty($currentDynamicContent)) {
            $currentDynamicContent = new DynamicContent();
            $currentDynamicContent->setTitle($title);
            $em->persist($currentDynamicContent);
        }

        // Sinon, on modifie le contenu existant par le nouveau contenu
        $form = $this->createForm(DynamicContentFormType::class, $currentDynamicContent);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $doctrine->getManager();
            $em->flush();

            $this->addFlash('success', 'Le contenu a bien été modifié !');

            return $this->redirectToRoute("main_home");

        }

        return $this->render('main/dynamic_content_edit.html.twig', [
            'form' => $form->createView(),]);
    }

    #[Route('/le-twist', name: 'about')]
    public function about(ManagerRegistry $doctrine): \Symfony\Component\HttpFoundation\Response
    {
        $aboutRepo = $doctrine ->getRepository(About::class);
        $about = $aboutRepo->findOneById(1);

        $aboutCarrouselImagesRepo = $doctrine->getRepository(AboutCarrouselImage::class);
        $aboutCarrouselImages = $aboutCarrouselImagesRepo->findAll();



        return $this->render('main/about.html.twig', [
            'about' => $about,
            'aboutCarrouselImages' => $aboutCarrouselImages

        ]);
    }

    #[Route("//le-twist/modifier/{id}/", name: 'about_edit', priority: 10)]
    #[isGranted("ROLE_ADMIN")]
    public function publicationEdit(About $about, Request $request, ManagerRegistry $doctrine): \Symfony\Component\HttpFoundation\Response
    {

        $form = $this->createForm(AboutFormType::class, $about);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $coverImage = $form->get('coverImage')->getData();

            if ($coverImage == null){

                $em = $doctrine->getManager();
                $em->flush();

            } else {

                if (
                    $about->getCoverImage() != null &&
                    file_exists($this->getParameter('app.about.image.folder') . $about->getCoverImage())
                ) {
                    unlink($this->getParameter('app.about.image.folder') . $about->getCoverImage());
                }


                /*Génération nom*/
                do {
                    $newFileName = md5(random_bytes(100)) . '.' . $coverImage->guessExtension();
                } while (file_exists($this->getParameter('app.about.image.folder') . $newFileName));

                $about->setcoverImage($newFileName);

                $em = $doctrine->getManager();
                $em->flush();

                $coverImage->move(
                    $this->getParameter('app.about.image.folder'),
                    $newFileName,
                );
            }
            $this->addFlash('success', 'Page de présentation modifiée avec succès');
            return $this->redirectToRoute('main_about', [
                'id' => $about->getId(),
            ]);
        }

        return $this->render('main/about_edit.html.twig', ['form' => $form->createView(),]);
    }

    #[Route('/le-twist/ajouter-photo-carrousel', name: 'about_add_carousel_image')]
    #[isGranted('ROLE_ADMIN')]
    public function addAboutCarouselImage(Request $request, ManagerRegistry $doctrine): \Symfony\Component\HttpFoundation\Response
    {
        $aboutCarouselImage = new AboutCarrouselImage();
        $form=$this->createForm(AboutCarrouselImageFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $aboutCarouselImage->setName($form->get('name')->getData());
            $image = $form->get('image')->getData();

            if(
                $aboutCarouselImage->getImage() != null &&
                file_exists($this->getParameter('app.about.carrousel.image.folder') . $aboutCarouselImage->getImage() )
            ){
                unlink($this->getParameter('app.about.carrousel.image.folder') . $aboutCarouselImage->getImage() );
            }


            /*Génération nom*/
            do{
                $newFileName = md5( random_bytes(100) ) . '.' . $image->guessExtension();
            } while (file_exists($this->getParameter('app.about.carrousel.image.folder') .$newFileName));

            $aboutCarouselImage->setImage($newFileName);

            $em = $doctrine->getManager();
            $em->persist($aboutCarouselImage);
            $em->flush();

            $image -> move(
                $this->getParameter('app.about.carrousel.image.folder'),
                $newFileName,
            );

            $this->addFlash('success', 'Photo du carrousel ajoutée avec succès');

            return $this->redirectToRoute('main_about');
        }

        return $this->render('main/add_about_carousel_image.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/le-twist/editer-photo-carrousel/{id}', name: 'about_edit_carousel_picture')]
    #[isGranted('ROLE_ADMIN')]
    public function editAboutCarouselPicture(Request $request, ManagerRegistry $doctrine, AboutCarrouselImage $aboutCarrouselImage): \Symfony\Component\HttpFoundation\Response
    {

        $form = $this->createForm(AboutCarrouselImageFormType::class);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

            if (
                $aboutCarrouselImage->getImage() != null &&
                file_exists($this->getParameter('app.about.carrousel.image.folder') . $aboutCarrouselImage->getImage())
            ) {
                unlink($this->getParameter('app.about.carrousel.image.folder') . $aboutCarrouselImage->getImage());
            }


            /*Génération nom*/
            do {
                $newFileName = md5(random_bytes(100)) . '.' . $image->guessExtension();
            } while (file_exists($this->getParameter('app.about.carrousel.image.folder') . $newFileName));

            $aboutCarrouselImage->setImage($newFileName);

            $em = $doctrine->getManager();
            $em->flush();

            $image->move(
                $this->getParameter('app.about.carrousel.image.folder'),
                $newFileName,
            );

            $this->addFlash('success', 'Photo du carrousel modifiée avec succès');

            return $this->redirectToRoute('main_about', [
                'id' => $aboutCarrouselImage->getId(),
            ]);
        }

        return $this->render('main/edit_about_carousel_image.html.twig', [
            'form' => $form->createView(),
            'about_carousel_image' => $aboutCarrouselImage,
        ]);
    }

    #[Route('/le-twist/supprimer-photo-carrousel/{id}', name: 'delete_about_carousel_picture', priority: 10)]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteAboutCarouselPicture(AboutCarrouselImage $aboutCarrouselImage, Request $request, ManagerRegistry $doctrine): \Symfony\Component\HttpFoundation\RedirectResponse
    {
//        Token CSRF
        $csrfToken = $request->query->get('csrf_token', '');

        if (!$this->isCsrfTokenValid('delete_about_carousel_image' . $aboutCarrouselImage->getId(), $csrfToken)) {

            $this->addFlash('error','Token de sécurité invalide, veuillez ré-essayer.');
        }
        else {
            unlink($this->getParameter('app.about.carrousel.image.folder') . $aboutCarrouselImage->getImage());

            // Suppression de l'image en BDD
            $em = $doctrine->getManager();
            $em->remove($aboutCarrouselImage);
            $em->flush();

            // Message flash de succès
            $this->addFlash('success', "La photo a été supprimée avec succès !");
        }
        // Redirection vers la page qui liste les events
        return $this->redirectToRoute('main_about', [
            'about_carrousel_image' => $aboutCarrouselImage,
        ]);
    }


    #[Route('/contactez-moi', name: 'contact')]
    public function contact(): \Symfony\Component\HttpFoundation\Response
    {

        return $this->render('main/contact.html.twig');
    }
}
