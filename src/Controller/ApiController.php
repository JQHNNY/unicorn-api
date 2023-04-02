<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Unicorn;
use App\Repository\PostRepository;
use App\Repository\UnicornRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\MailerService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    public function __construct(
        public EntityManagerInterface $entityManager,
        public SerializerInterface    $serializer,
        public PostRepository         $postRepository,
        public UnicornRepository      $unicornRepository,
        public MailerService          $mailerService
    )
    {
    }

    #[Route('/unicorns', name: 'unicorns', methods: 'GET')]
    #[OA\Response(
        response: 200,
        description: 'Returns a list of all unicorns',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Unicorn::class, groups: ['unicorn']))
        )
    )]
    #[OA\Tag(name: 'Unicorns')]
    public function unicorns(): JsonResponse
    {
        $unicorns = $this->unicornRepository->findAll();
        $json = $this->serializer->serialize($unicorns, 'json', ['groups' => ['unicorn'], 'json_encode_options' => JSON_PRETTY_PRINT]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/posts', name: 'posts', methods: 'GET')]
    #[OA\Response(
        response: 200,
        description: 'Returns a list of all posts',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Post::class, groups: ['post']))
        )
    )]
    #[OA\Tag(name: 'Posts')]
    public function posts(): JsonResponse
    {
        $posts = $this->postRepository->findAll();
        $json = $this->serializer->serialize($posts, 'json', ['groups' => ['post', 'unicorn'], 'json_encode_options' => JSON_PRETTY_PRINT]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/posts/create/{id<\d+>}', name: 'create_post', methods: 'POST')]
    #[OA\RequestBody(
        description: 'Message and author for creating the post',
        required: 'true',
        content: [new OA\JsonContent(
            required: ['author', 'message'],
            type: 'object',
            example: '{"author": "Jeff", "message": "This is a lovely unicorn"}'
        )]
    )]
    #[OA\Response(
        response: 200,
        description: 'Creates a post attached to unicorn',
        content: new OA\JsonContent(
            type: 'object',
            example: '{"message": "Post has been created!"}'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Unicorn not found',
    )]
    #[OA\Response(
        response: 400,
        description: 'Author or message is missing from the request body',
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'The unicorn id',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'Posts')]
    public function createPost(Request $request, int $id): JsonResponse
    {
        try {
            $json = $request->getContent();
            $unicorn = $this->unicornRepository->findOneBy(['id' => $id]);

            if (!$unicorn) {
                throw new NotFoundHttpException(sprintf('The unicorn with id: %s could not be found', $id));
            }

            if (!$json) {
                throw new BadRequestException('Request body is empty');
            }

            $post = $this->serializer->deserialize($json, Post::class, 'json');
            if (!$post->getAuthor() || !$post->getMessage()) {
                throw new BadRequestException('Author or message is missing from the request body');
            }
            $post->setUnicorn($unicorn);

            $this->entityManager->persist($post);
            $this->entityManager->flush();

        } catch (ExceptionInterface $e) {
            throw new BadRequestException(sprintf('Something went wrong: %s', $e->getMessage()));
        }

        return new JsonResponse([
            'message' => 'Post has been created!'
        ], Response::HTTP_OK);
    }

    #[Route('/posts/edit/{id<\d+>}', name: 'edit_post', methods: 'PUT')]
    #[OA\RequestBody(
        description: 'New message object for editing the post',
        required: 'true',
        content: [new OA\JsonContent(
            required: ['message'],
            type: 'object',
            example: '{"message": "I changed my mind, this unicorn is not as fabulous"}'
        )]
    )]
    #[OA\Response(
        response: 404,
        description: 'Post not found',
    )]
    #[OA\Response(
        response: 400,
        description: 'New message cannot be the same as old message, Message is missing from request body',
    )]
    #[OA\Response(
        response: 200,
        description: 'Edit the message from a post',
        content: [new OA\JsonContent(
            type: 'object',
            example: '{"message": "Post with id: 1 has been edited! Old message: This is a lovely unicorn, New message: I changed my mind, this unicorn is not as fabulous"}'
        )]
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'The post id',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'Posts')]
    public function editPost(int $id, Request $request): JsonResponse
    {
        $json = $request->getContent();
        $post = $this->postRepository->findOneBy(['id' => $id]);

        try {
            if (!$post) {
                throw new NotFoundHttpException(sprintf('The post with id: %s could not be found', $id));
            }
            $oldMessage = $post->getMessage();

            if (!$json) {
                throw new BadRequestException('Request body is empty');
            }
            $json = json_decode($json, true);

            if (!isset($json['message'])) {
                throw new BadRequestException('Message is missing from the request body');
            }

            if ($json['message'] && $json['message'] === $oldMessage) {
                throw new BadRequestException('New message cannot be the same as old message');
            }

            $post->setMessage($json['message']);

            $this->entityManager->persist($post);
            $this->entityManager->flush();

        } catch (ExceptionInterface $e) {
            throw new BadRequestException(sprintf('Something went wrong: %s', $e->getMessage()));
        }

        return new JsonResponse([
            'message' => sprintf(
                'Post with id: %s has been edited! Old message: %s, New message: %s',
                $post->getId(),
                $oldMessage,
                $post->getMessage()
            )
        ], Response::HTTP_OK);
    }

    #[Route('/posts/remove/{id}', name: 'remove_post', methods: 'DELETE')]
    #[OA\Parameter(
        name: 'id',
        description: 'The post id',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 404,
        description: 'Post not found',
    )]
    #[OA\Response(
        response: 200,
        description: 'Removes the post',
        content: [new OA\JsonContent(
            type: 'object',
            example: '{"message": "Post with id: 1 has been deleted!"}'
        )]
    )]
    #[OA\Tag(name: 'Posts')]
    public function removePost(int $id): JsonResponse
    {
        try {
            $post = $this->postRepository->findOneBy(['id' => $id]);

            if (!$post) {
                throw new NotFoundHttpException(sprintf('The post with id: %s could not be found', $id));
            }

            $this->entityManager->remove($post);
            $this->entityManager->flush();
        } catch (ExceptionInterface $e) {
            throw new BadRequestException(sprintf('Something went wrong: %s', $e->getMessage()));
        }

        return new JsonResponse([
            'message' => sprintf('Post with id: %s has been deleted!', $id)
        ], Response::HTTP_OK);
    }

    #[Route('/unicorns/purchase/{id}', name: 'purchase_unicorn', methods: 'POST')]
    #[OA\RequestBody(
        description: 'Send email containing unicorn and all posts made about your unicorn',
        required: 'true',
        content: [new OA\JsonContent(
            required: ['email'],
            type: 'object',
            example: '{"email": "jeff.green@nodomain.com"}'
        )]
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'The unicorn id',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]

    #[OA\Response(
        response: 400,
        description: 'Email is missing from the request body, invalid email, invalid request body',
    )]
    #[OA\Response(
        response: 404,
        description: 'Unicorn not found',
    )]
    #[OA\Response(
        response: 200,
        description: 'Purchases the unicorn and sends an email with all posts linked to the unicorn',
        content: [new OA\JsonContent(
            type: 'object',
            example: '{"message":"Unicorn with id: 4 has been purchased and an email has been sent to jeff.green@nodomain.com"}'
        )]
    )]
    #[OA\Tag(name: 'Unicorns')]
    public function purchaseUnicorn(int $id, Request $request): JsonResponse
    {
        $json = $request->getContent();
        try {
            $unicorn = $this->unicornRepository->findOneBy(['id' => $id]);
            if (!$unicorn) {
                throw new NotFoundHttpException(sprintf('The unicorn with id: %s could not be found', $id));
            }
            $posts = $this->postRepository->findBy(['unicorn' => $unicorn]);
            if (!$json) {
                throw new BadRequestException('Request body is invalid');
            }
            $json = json_decode($json, true);
            if (!isset($json['email'])) {
                throw new BadRequestException('Email is missing from the request body');
            }

            if (!filter_var($json['email'], FILTER_VALIDATE_EMAIL)) {
                throw new BadRequestException('Email is not valid');
            }

            $emailTo = $json['email'];
            $html = $this->buildHtml($unicorn, $posts);

            $this->mailerService->sendEmail($emailTo, $unicorn, $html);
            $this->entityManager->flush();

        } catch (ExceptionInterface $e) {
            throw new BadRequestException(sprintf('Something went wrong: %s', $e->getMessage()));
        }
        return new JsonResponse([
            'message' => sprintf('Unicorn with id: %s has been purchased and an email has been sent to %s', $id, $emailTo)
        ], Response::HTTP_OK);
    }

    public function buildHtml(Unicorn $unicorn, array $posts): string
    {
        $html = '<h1>Hello, you just purchased a unicorn named: ' . $unicorn->getName() . '!</h1>';

        if (!$posts) {
            $html .= '<p>There are no posts about your unicorn.</p>';
        } else {
            $html .= '<h2>Here is a list of all posts made about your lovely unicorn</h2><ul>';
            foreach ($posts as $post) {
                $html .= '<li>' . $post->getAuthor() . ' - ' . $post->getMessage() . '</li>';
            }
            $html .= '</ul>';
        }

        return $html;
    }
}
