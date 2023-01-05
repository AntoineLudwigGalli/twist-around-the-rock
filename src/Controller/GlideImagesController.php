<?php

namespace App\Controller;

use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use League\Glide\Urls\UrlBuilderFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use League\Glide\ServerFactory;
use League\Glide\Responses\SymfonyResponseFactory;

class GlideImagesController extends AbstractController
{
    /**
     * @throws SignatureException
     */
    #[Route('/image/{path}', name: 'app_glide_images')]
    public function glideImages(Request $request, $path): Response
    {
        $server = ServerFactory::create([
            'source' => $this->getParameter("app.public_dir"),
            'cache' => $this->getParameter("app.image_cache_dir"),
            'base_url' => "image",

            'response' => new SymfonyResponseFactory(),
        ]);

//todo ajouter sécurité Glide

        return $server->getImageResponse($path, $request->query->all());
    }

    #[Route('/image/carrousel_images/{path}', name: 'app_glide_carrousel_images')]
    public function glideCarrouselImages(Request $request, $path): Response
    {
        $server = ServerFactory::create([
            'source' => $this->getParameter("app.public_dir.carrousel_images"),
            'cache' => $this->getParameter("app.image_cache_dir.carrousel_images"),
            'base_url' => "image",

            'response' => new SymfonyResponseFactory(),
        ]);

//todo ajouter sécurité Glide

        return $server->getImageResponse($path, $request->query->all());
    }

    #[Route('/image/about_carrousel_images/{path}', name: 'app_glide_about_carrousel_images')]
    public function glideAboutCarrouselImages(Request $request, $path): Response
    {
        $server = ServerFactory::create([
            'source' => $this->getParameter("app.public_dir.about_carrousel_images"),
            'cache' => $this->getParameter("app.image_cache_dir.about_carrousel_images"),
            'base_url' => "image",

            'response' => new SymfonyResponseFactory(),
        ]);

//todo ajouter sécurité Glide

        return $server->getImageResponse($path, $request->query->all());
    }

    #[Route('/image/about_images/{path}', name: 'app_glide_about_images')]
    public function glideAboutImages(Request $request, $path): Response
    {
        $server = ServerFactory::create([
            'source' => $this->getParameter("app.public_dir.about_images"),
            'cache' => $this->getParameter("app.image_cache_dir.about_images"),
            'base_url' => "image",

            'response' => new SymfonyResponseFactory(),
        ]);

//todo ajouter sécurité Glide

        return $server->getImageResponse($path, $request->query->all());
    }

    #[Route('/image/article_carrousel_images/{path}', name: 'app_glide_article_carrousel_images')]
    public function glideArticleCarrouselImages(Request $request, $path): Response
    {
        $server = ServerFactory::create([
            'source' => $this->getParameter("app.public_dir.article_carrousel_images"),
            'cache' => $this->getParameter("app.image_cache_dir.article_carrousel_images"),
            'base_url' => "image",

            'response' => new SymfonyResponseFactory(),
        ]);

//todo ajouter sécurité Glide

        return $server->getImageResponse($path, $request->query->all());
    }

    #[Route('/image/article_images/{path}', name: 'app_glide_article_images')]
    public function glideArticleImages(Request $request, $path): Response
    {
        $server = ServerFactory::create([
            'source' => $this->getParameter("app.public_dir.article_images"),
            'cache' => $this->getParameter("app.image_cache_dir.article_images"),
            'base_url' => "image",

            'response' => new SymfonyResponseFactory(),
        ]);

//todo ajouter sécurité Glide

        return $server->getImageResponse($path, $request->query->all());
    }

    #[Route('/image/product_carrousel_images/{path}', name: 'app_glide_product_carrousel_images')]
    public function glideProductCarrouselImages(Request $request, $path): Response
    {
        $server = ServerFactory::create([
            'source' => $this->getParameter("app.public_dir.product_carrousel_images"),
            'cache' => $this->getParameter("app.image_cache_dir.product_carrousel_images"),
            'base_url' => "image",

            'response' => new SymfonyResponseFactory(),
        ]);

//todo ajouter sécurité Glide

        return $server->getImageResponse($path, $request->query->all());
    }

    #[Route('/image/product_images/{path}', name: 'app_glide_product_images')]
    public function glideProductImages(Request $request, $path): Response
    {
        $server = ServerFactory::create([
            'source' => $this->getParameter("app.public_dir.product_images"),
            'cache' => $this->getParameter("app.image_cache_dir.product_images"),
            'base_url' => "image",

            'response' => new SymfonyResponseFactory(),
        ]);

//todo ajouter sécurité Glide

        return $server->getImageResponse($path, $request->query->all());
    }
}
