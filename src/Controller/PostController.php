<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


#[Route('/post')]
#[IsGranted('ROLE_USER')]
final class PostController extends AbstractController
{
    #[Route(name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $post->setPostDate(new \DateTime());
            


            $imgFile = $form->get('img')->getData();

            // this condition is needed because the 'img' field is not required
            // so the img file must be processed only when a file is uploaded
            if ($imgFile) {
                $directory = $this->getParameter('img_directory');
                $originalFilename = pathinfo($imgFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imgFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imgFile->move($directory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'imgFile $imgFilename' property to store the img file name
                // instead of its contents
                $post->setImg($newFilename);
            }
            $entityManager->persist($post);
            $entityManager->flush();
            // ... persist the $product variable or any other work

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/like/{id}', name: 'app_post_like', methods: ['GET'])]
    public function like(Post $post, EntityManagerInterface $entityManager): Response
    {
        $post->addLike($this->getUser());
        $entityManager->persist($post);
        $entityManager->flush();
        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/unlike/{id}', name: 'app_post_unlike', methods: ['GET'])]
    public function unlike(Post $post, EntityManagerInterface $entityManager): Response
    {
        $post->removeLike($this->getUser());
        $entityManager->persist($post);
        $entityManager->flush();
        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/user/{id}', name: 'app_post_share', methods: ['GET'])]
    public function share(Post $post, EntityManagerInterface $entityManager): Response
    {


        $this->getUser()->addCompartido($post);
        $entityManager->persist($post);
        $entityManager->flush();
        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/user/unshare/{id}', name: 'app_post_unshare', methods: ['GET'])]
    public function unshare(Post $post, EntityManagerInterface $entityManager): Response
    {
        $this->getUser()->removeCompartido($post);
        $entityManager->persist($post);
        $entityManager->flush();
        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
