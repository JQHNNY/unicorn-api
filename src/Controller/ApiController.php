<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\UnicornRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    public function __construct(
        public EntityManagerInterface $entityManager,
        public SerializerInterface $serializer,
        public PostRepository $postRepository,
        public UnicornRepository $unicornRepository,
        public MailerInterface $mailer
    ) {}

    #[Route('/unicorns', name: 'unicorns', methods: 'GET')]
    public function unicorns(): JsonResponse
    {
        $unicorns = $this->unicornRepository->findAll();
        $json = $this->serializer->serialize($unicorns, 'json', ['groups' => ['unicorn'],'json_encode_options' => JSON_PRETTY_PRINT]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/posts', name: 'posts', methods: 'GET')]
    public function posts(): JsonResponse
    {
        $posts = $this->postRepository->findAll();
        $json = $this->serializer->serialize($posts, 'json', ['groups' => ['post', 'unicorn'], 'json_encode_options' => JSON_PRETTY_PRINT]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/create/post/{id<\d+>}', name: 'create_post', methods: 'POST')]
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
        ]);
    }

    #[Route('/edit/post/{id<\d+>}', name: 'edit_post', methods: 'POST')]
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
            $json = json_decode($json,true);
            if ($json['message'] && $json['message'] === $oldMessage) {
                throw new BadRequestException('New message cannot be the same as old message');
            }

            $post->setMessage($json['message']);

            $this->entityManager->persist($post);
            $this->entityManager->flush();

        }
        catch (ExceptionInterface $e) {
            throw new BadRequestException(sprintf('Something went wrong: %s', $e->getMessage()));
        }

        return new JsonResponse([
            'message' => sprintf(
                'Post with id: %s has been edited! Old message: %s, New message: %s',
                $post->getId(),
                $oldMessage,
                $post->getMessage()
            )
        ]);
    }

    #[Route('/remove/post/{id}', name: 'remove_post', methods: 'DELETE')]
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
        ]);
    }

    #[Route('/unicorns/purchase/{id}', name: 'purchase_unicorn', methods: 'POST')]
    public function purchaseUnicorn(int $id, Request $request): JsonResponse
    {
        $json = json_decode($request->getContent(), true);
        try {
            $unicorn = $this->unicornRepository->findOneBy(['id' => $id]);
            if (!$unicorn) {
                throw new NotFoundHttpException(sprintf('The unicorn with id: %s could not be found', $id));
            }
            $posts = $this->postRepository->findBy(['unicorn' => $unicorn]);
            if (!$json) {
                throw new BadRequestException('Request body is invalid');
            }
            if (!$json && !isset($json['email'])) {
                throw new BadRequestException('Email is missing from the request body');
            }

            if (!filter_var($json['email'], FILTER_VALIDATE_EMAIL)) {
                throw new BadRequestException('Email is not valid');
            }
            $emailTo = $json['email'];

            $html = '<h1>Hello, you just purchased a unicorn named: ' . $unicorn->getName() . '!</h1>';
            if (!$posts) {
                $html .= '<p>There are no posts about your unicorn.</p>';
            } else {
                $html .= '<h2>Here is a list of all posts made about your lovely unicorn</h2><ul>';
                foreach ($posts as $post) {
                    $html .= '<li>' . $post->getAuthor() . ' - ' . $post->getMessage() . '</li>';
                    $this->entityManager->remove($post);
                }
                $html .= '</ul>';
            }
            $email = (new Email())
                ->from('unicornfarm@special.be')
                ->to($emailTo)
                ->subject('Purchase ' . $unicorn->getName())
                ->html($html);

            $this->mailer->send($email);
            $this->entityManager->flush();
        } catch (ExceptionInterface $e) {
            throw new BadRequestException(sprintf('Something went wrong: %s', $e->getMessage()));
        }
        return new JsonResponse([
            'message' => sprintf('Unicorn with id: %s has been purchased and an email has been sent to %s', $id, $emailTo)
        ]);
    }
}
