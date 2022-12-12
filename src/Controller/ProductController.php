<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Product;
use App\Entity\ProductCarrouselImage;
use App\Form\ProductCarrouselImageFormType;
use App\Form\ProductFormType;
use App\Form\SearchFormType;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/produits', name: 'products_')]
class ProductController extends AbstractController
{
    #[Route('/', name: '')]
    public function productsList(Request $request, ProductRepository $repository): Response
    {
        $data = new SearchData();
        $data->page = $request->get('page', 1);

        $form = $this->createForm(SearchFormType::class, $data);
        $form->handleRequest($request);
        [$min, $max] = $repository->findMinMax($data);

        $products = $repository->findSearch($data);


//        if ($request->get('ajax')) {
//            return new JsonResponse([
//                'content' => $this->renderView('product/product_card.html.twig', ['products' => $products]),
//                'sorting' => $this->renderView('product/product_sorting.html.twig', ['products' => $products]),
//                'pagination' => $this->renderView('product/product_pagination.html.twig', ['products' => $products]),
//                'pages' => ceil($products->getTotalItemCount() / $products->getItemNumberPerPage()),
//                'min' => $min,
//                'max' => $max
//            ]);
//        }

        return $this->render('product/products_list.html.twig', [
            'products' => $products,
            'form' => $form->createView(),
            'min' => $min,
            'max' => $max,
        ]);
    }

    /**
     * Contrôleur de la page permettant de voir un produit en détail (via ID et slug dans l'URL)
     */

    #[Route('/{id}/{slug}/', name: 'view')]
    #[ParamConverter('product', options: ['mapping' => ['id' => 'id', 'slug' => 'slug']])]
    public function publicationView(Product $product, Request $request, ManagerRegistry $doctrine): Response
    {

        $productCarrouselImagesRepo = $doctrine->getRepository(ProductCarrouselImage::class);
        $productCarrouselImages = $productCarrouselImagesRepo->findBy(['product' => $product->getId()]);

        return $this->render('product/product_view.html.twig', [
            'product' => $product,
            'productCarrouselImages' => $productCarrouselImages
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

        return $this->redirectToRoute('admin_products_list');
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

    #[Route('{id}/ajouter-photo-carrousel', name: 'add_carousel_image', requirements: ["id" => "\d+"] )]
    #[isGranted('ROLE_ADMIN')]
    public function addProductCarouselImage(Request $request, ManagerRegistry $doctrine, Product $product): \Symfony\Component\HttpFoundation\Response
    {
        $productCarouselImage = new ProductCarrouselImage();
        $form=$this->createForm(ProductCarrouselImageFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $productCarouselImage->setName($form->get('name')->getData());
            $productCarouselImage->setProduct($product);
            $image = $form->get('image')->getData();

            if(
                $productCarouselImage->getImage() != null &&
                file_exists($this->getParameter('app.product.carrousel.image.folder') . $productCarouselImage->getImage() )
            ){
                unlink($this->getParameter('app.product.carrousel.image.folder') . $productCarouselImage->getImage() );
            }


            /*Génération nom*/
            do{
                $newFileName = md5( random_bytes(100) ) . '.' . $image->guessExtension();
            } while (file_exists($this->getParameter('app.product.carrousel.image.folder') .$newFileName));

            $productCarouselImage->setImage($newFileName);

            $em = $doctrine->getManager();
            $em->persist($productCarouselImage);
            $em->flush();

            $image -> move(
                $this->getParameter('app.product.carrousel.image.folder'),
                $newFileName,
            );

            $this->addFlash('success', 'Photo du carrousel ajoutée avec succès');

            return $this->redirectToRoute('products_view', [
                'id' => $product->getId(),
                'slug' => $product->getSlug(),
            ]);
        }

        return $this->render('product/add_product_carousel_image.html.twig', [
            'form' => $form->createView(),
            'id' => $product->getId()
        ]);
    }

    #[Route('{productId}/editer-photo-carrousel/{id}', name: 'edit_carousel_image')]
    #[isGranted('ROLE_ADMIN')]
    public function editProductCarouselPicture(Request $request, ManagerRegistry $doctrine, ProductCarrouselImage $productCarrouselImage): \Symfony\Component\HttpFoundation\Response
    {

        $form = $this->createForm(ProductCarrouselImageFormType::class);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

            if (
                $productCarrouselImage->getImage() != null &&
                file_exists($this->getParameter('app.product.carrousel.image.folder') . $productCarrouselImage->getImage())
            ) {
                unlink($this->getParameter('app.product.carrousel.image.folder') . $productCarrouselImage->getImage());
            }


            /*Génération nom*/
            do {
                $newFileName = md5(random_bytes(100)) . '.' . $image->guessExtension();
            } while (file_exists($this->getParameter('app.product.carrousel.image.folder') . $newFileName));

            $productCarrouselImage->setImage($newFileName);

            $em = $doctrine->getManager();
            $em->flush();

            $image->move(
                $this->getParameter('app.product.carrousel.image.folder'),
                $newFileName,
            );

            $this->addFlash('success', 'Photo du carrousel modifiée avec succès');

            return $this->redirectToRoute('products_view', [
                'id' => $productCarrouselImage->getProduct()->getId(),
                'slug' => $productCarrouselImage->getProduct()->getSlug(),
            ]);
        }

        return $this->render('product/edit_product_carrousel_image.html.twig', [
            'form' => $form->createView(),
            'product_carousel_image' => $productCarrouselImage,
            'productId' => $productCarrouselImage->getProduct(),
        ]);
    }

    #[Route('/supprimer-photo-carrousel/{id}', name: 'delete_carousel_image', priority: 10)]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteProductCarouselPicture(ProductCarrouselImage $productCarrouselImage, Request $request, ManagerRegistry $doctrine): \Symfony\Component\HttpFoundation\RedirectResponse
    {
//        Token CSRF
        $csrfToken = $request->query->get('csrf_token', '');

        if (!$this->isCsrfTokenValid('delete_product_carousel_image' . $productCarrouselImage->getId(), $csrfToken)) {

            $this->addFlash('error','Token de sécurité invalide, veuillez ré-essayer.');
        }
        else {
            unlink($this->getParameter('app.product.carrousel.image.folder') . $productCarrouselImage->getImage());

            // Suppression de l'image en BDD
            $em = $doctrine->getManager();
            $em->remove($productCarrouselImage);
            $em->flush();

            // Message flash de succès
            $this->addFlash('success', "La photo a été supprimée avec succès !");
        }
        // Redirection vers la page qui liste les events
        return $this->redirectToRoute('products_view', [
            'product_carrousel_image' => $productCarrouselImage,
            'id' => $productCarrouselImage->getProduct()->getId(),
            'slug' => $productCarrouselImage->getProduct()->getSlug(),
        ]);
    }
}
