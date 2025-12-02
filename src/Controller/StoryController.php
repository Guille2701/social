<?php

namespace App\Controller;

use App\Entity\Story;
use App\Form\StoryType;
use App\Repository\StoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/story')]
final class StoryController extends AbstractController
{
    #[Route(name: 'app_story_index', methods: ['GET'])]
    public function index(StoryRepository $storyRepository): Response
    {
        return $this->render('story/index.html.twig', [
            'stories' => $storyRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_story_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $story = new Story();
        $form = $this->createForm(StoryType::class, $story);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $story->setAuthor($this->getUser());
            $story->setCreationDate(new \DateTime());



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
                $story->setImg($newFilename);
            }
            $entityManager->persist($story);
            $entityManager->flush();
            // ... persist the $product variable or any other work

            return $this->redirectToRoute('app_story_index', [], Response::HTTP_SEE_OTHER);
        }



        return $this->render('story/new.html.twig', [
            'story' => $story,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_story_show', methods: ['GET'])]
    public function show(Story $story): Response
    {
        return $this->render('story/show.html.twig', [
            'story' => $story,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_story_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Story $story, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StoryType::class, $story);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_story_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('story/edit.html.twig', [
            'story' => $story,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_story_delete', methods: ['POST'])]
    public function delete(Request $request, Story $story, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$story->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($story);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_story_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/likeS/{id}', name: 'app_story_like', methods: ['GET'])]
    public function like(Story $story, EntityManagerInterface $entityManager): Response
    {
        $story->addLike($this->getUser());
        $entityManager->persist($story);
        $entityManager->flush();
        return $this->redirectToRoute('app_story_index', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/unlikeS/{id}', name: 'app_story_unlike', methods: ['GET'])]
    public function unlike(Story $story, EntityManagerInterface $entityManager): Response
    {
        $story->removeLike($this->getUser());
        $entityManager->persist($story);
        $entityManager->flush();
        return $this->redirectToRoute('app_story_index', [], Response::HTTP_SEE_OTHER);
    }

    
}
