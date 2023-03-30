<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UnicornRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    #[Route('/unicorns', name: 'unicorns', methods: 'GET')]
    public function unicorns(UnicornRepository $unicornRepository, SerializerInterface $serializer): JsonResponse
    {
        $unicorns = $unicornRepository->findAll();

        return new JsonResponse($serializer->serialize($unicorns, 'json'));
    }

    #[Route('/posts', name: 'posts', methods: 'GET')]
    public function posts(PostRepository $postRepository, SerializerInterface $serializer): JsonResponse
    {
        $posts = $postRepository->findAll();

        return new JsonResponse($serializer->serialize($posts, 'json'));
    }

    #[Route('/create/post', name: 'create_post', methods: 'POST')]
    public function createPost(UnicornRepository $unicornRepository): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiController.php',
        ]);
    }

    #[Route('/edit/post/{id}', name: 'edit_post', methods: 'POST')]
    public function editPost(PostRepository $postRepository): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiController.php',
        ]);
    }

    #[Route('/remove/post/{id}', name: 'remove_post', methods: 'POST')]
    public function removePost(PostRepository $postRepository): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiController.php',
        ]);
    }

    #[Route('/unicorns/purchase/{id}', name: 'purchase_unicorn', methods: 'POST')]
    public function purchaseUnicorn(PostRepository $postRepository): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiController.php',
        ]);
    }
}
