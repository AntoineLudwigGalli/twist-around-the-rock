<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Product;
use App\Form\ProductFormType;
use App\Form\SearchFormType;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/produits', name: 'products_')]
class ProductController extends AbstractController
{
    #[Route('/', name: '')]
    public function productsList(ManagerRegistry $doctrine, Request $request, ProductRepository $repository): Response
    {
        $data = new SearchData();
        $form = $this->createForm(SearchFormType::class, $data);
        $products = $repository->findSearch();

//        $requestedPage = $request->query->getInt('page', 1);
//
//        if ($requestedPage < 1) {
//            throw new NotFoundHttpException();
//        }
//
//        $em = $doctrine->getManager();
//
//        $query = $em->createQuery('SELECT p FROM App\Entity\Product p ORDER BY p.creationDate DESC');
//        $products = $paginator->paginate($query, //Requête créée juste avant
//            $requestedPage, // Page qu'on souhaite voir
//            8, // Nombre d'articles à afficher par page
//        );
        return $this->render('product/products_list.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/recherche/', name: 'search')]
    public function search(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {

        // Récupération du numéro de la page demandée dans l'url (si il existe pas, 1 sera pris à la place)
        $requestedPage = $request->query->getInt('page', 1);

        // Si la page demandée est inférieur à 1, erreur 404
        if ($requestedPage < 1) {
            throw new NotFoundHttpException();
        }

        // On récupère la recherche de l'utilisateur depuis l'url ($_GET['q'])
        $search = $request->query->get('s', '');

        // Récupération du manager général des entités
        $em = $doctrine->getManager();

        // Création d'une requête permettant de récupérer les produits pour la page actuelle, dont le titre ou le contenu contient la recherche de l'utilisateur
        $query = $em
            ->createQuery('SELECT p FROM App\Entity\Product p WHERE p.name LIKE :search OR p.content LIKE :search ORDER BY p.creationDate DESC')
            ->setParameters([
                'search' => '%' . $search . '%',
            ])
        ;

        // Récupération des produits
        $products = $paginator->paginate(
            $query,
            $requestedPage,
            10
        );

        // Appel de la vue en lui envoyant les produits à afficher
        return $this->render('product/list_search.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * Contrôleur de la page permettant de voir un produit en détail (via ID et slug dans l'URL)
     */

    #[Route('/{id}/{slug}/', name: 'view')]
    #[ParamConverter('product', options: ['mapping' => ['id' => 'id', 'slug' => 'slug']])]
    public function publicationView(Product $product, Request $request, ManagerRegistry $doctrine): Response
    {

        return $this->render('product/product_view.html.twig', [
            'article' => $product,
        ]);
    }

    /**
     * Contrôleur de la page admin via son id dans l'url
     *
     * Accès réservé aux admins
     */
    #[Route("/suppression/{id}/", name: 'delete', priority: 10)]
    #[isGranted("ROLE_ADMIN")]
    public function publicationDelete(Product $product, Request $request, ManagerRegistry $doctrine): Response
    {
        $csrfToken = $request->query->get('csrf_token', '');

        if (!$this->isCsrfTokenValid('product_delete_' . $product->getId(), $csrfToken)) {
            $this->addFlash('error', 'Token de sécurité invalide, veuillez ré-essayer');
        }
        else {

            $em = $doctrine->getManager();
            $em->remove($product);
            $em->flush();

            $this->addFlash('success', 'Produit supprimé avec succès');
        }

        return $this->redirectToRoute('products_');
    }

    /**
     * Contrôleur de la page admin via son id dans l'url pour modifier un article
     *
     * Accès réservé aux admins
     */
    #[Route("/modifier/{id}/", name: 'edit', priority: 10)]
    #[isGranted("ROLE_ADMIN")]
    public function productEdit(Product $product, Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {

        $form = $this->createForm(ProductFormType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $product->setSlug($slugger->slug($product->getName())->lower());

            $coverImage = $form->get('coverImage')->getData();
            $illustrationImageRight = $form->get('illustrationImageRight')->getData();
            $illustrationImageLeft = $form->get('illustrationImageLeft')->getData();

            if ($coverImage == null && $illustrationImageRight == null && $illustrationImageLeft == null) {

                $em = $doctrine->getManager();
                $em->flush();

            }
            else {
                if ($coverImage != null) {
                    if (
                        $product->getcoverImage() != null &&
                        file_exists($this->getParameter('app.product.image.folder') . $product->getcoverImage())
                    ) {
                        unlink($this->getParameter('app.product.image.folder') . $product->getcoverImage());
                    }


                    /*Génération nom*/
                    do {
                        $newFileName = md5(random_bytes(100)) . '.' . $coverImage->guessExtension();
                    } while (file_exists($this->getParameter('app.product.image.folder') . $newFileName));

                    $product->setCoverImage($newFileName);

                    $coverImage->move(
                        $this->getParameter('app.product.image.folder'),
                        $newFileName,
                    );
                }

                if ($illustrationImageRight != null) {
                    if (
                        $product->getIllustrationImageRight() != null &&
                        file_exists($this->getParameter('app.product.image.folder') . $product->getIllustrationImageRight())
                    ) {
                        unlink($this->getParameter('app.product.image.folder') . $product->getIllustrationImageRight());
                    }
                    /*Génération nom*/
                    do {
                        $newFileName2 = md5(random_bytes(100)) . '.' . $illustrationImageRight->guessExtension();
                    } while (file_exists($this->getParameter('app.product.image.folder') . $newFileName2));

                    $product->setIllustrationImageRight($newFileName2);

                    $illustrationImageRight->move(
                        $this->getParameter('app.product.image.folder'),
                        $newFileName2,
                    );
                }


                if ($illustrationImageLeft != null) {
                    if (
                        $product->getIllustrationImageLeft() != null &&
                        file_exists($this->getParameter('app.product.image.folder') . $product->getIllustrationImageLeft())
                    ) {
                        unlink($this->getParameter('app.product.image.folder') . $product->getIllustrationImageLeft());
                    }

                    /*Génération nom*/
                    do {
                        $newFileName3 = md5(random_bytes(100)) . '.' . $illustrationImageLeft->guessExtension();
                    } while (file_exists($this->getParameter('app.product.image.folder') . $newFileName3));

                    $product->setIllustrationImageLeft($newFileName3);

                    $illustrationImageLeft->move(
                        $this->getParameter('app.product.image.folder'),
                        $newFileName3,
                    );
                }
            }

            $em = $doctrine->getManager();
            $em->flush();


            $this->addFlash('success', 'Produit modifié avec succès');
            return $this->redirectToRoute('products_view', [
                'id' => $product->getId(),
                'slug' => $product->getSlug(),]);
        }

        return $this->render('product/product_edit.html.twig', ['form' => $form->createView(),]);
    }

    #[Route('/nouveau-produit/', name: 'new_product')]
    #[isGranted('ROLE_ADMIN')]
    public function newProduct(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductFormType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setSlug($slugger->slug($product->getName())->lower());

            $coverImage = $form->get('coverImage')->getData();
            $illustrationImageRight = $form->get('illustrationImageRight')->getData();
            $illustrationImageLeft = $form->get('illustrationImageLeft')->getData();

            if (
                $coverImage != null
            ) {
                /*Génération nom*/
                do {
                    $newFileName = md5(random_bytes(100)) . '.' . $coverImage->guessExtension();
                } while (file_exists($this->getParameter('app.product.image.folder') . $newFileName));

                $product->setCoverImage($newFileName);

                $coverImage->move(
                    $this->getParameter('app.product.image.folder'),
                    $newFileName,
                );
            }

            if (
                $illustrationImageRight != null
            ) {

                /*Génération nom*/
                do {
                    $newFileName2 = md5(random_bytes(100)) . '.' . $illustrationImageRight->guessExtension();
                } while (file_exists($this->getParameter('app.product.image.folder') . $newFileName2));

                $product->setIllustrationImageRight($newFileName2);

                $illustrationImageRight->move(
                    $this->getParameter('app.product.image.folder'),
                    $newFileName2,
                );
            }

            if (
                $illustrationImageLeft != null
            ) {
                /*Génération nom*/
                do {
                    $newFileName3 = md5(random_bytes(100)) . '.' . $illustrationImageLeft->guessExtension();
                } while (file_exists($this->getParameter('app.product.image.folder') . $newFileName3));

                $product->setIllustrationImageLeft($newFileName3);

                $illustrationImageLeft->move(
                    $this->getParameter('app.product.image.folder'),
                    $newFileName3,
                );
            }

            $em = $doctrine->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');

            return $this->redirectToRoute('products_', [
                'id' => $product->getId(),
                'slug' => $product->getSlug(),]);
        }

        return $this->render('product/new_product.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
