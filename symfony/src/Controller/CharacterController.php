<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Character;
use App\Form\Type\CharacterType;
use App\Service\FileUploader;

class CharacterController extends AbstractController
{
    /**
     * @Route("/character", name="character_read_all")
     */
    public function index()
    {
        $characters = $this->getDoctrine()
            ->getRepository(Character::class)
            ->findAll();

        return $this->render('character/index.html.twig', [
            'characters' => $characters,
        ]);
    }

    /**
     * @Route("/character/read/id/{id}", name="character_read_one")
     */
    public function read($id)
    {
        $character = $this->getDoctrine()
            ->getRepository(Character::class)
            ->find($id);

        if (!$character) {
            throw $this->createNotFoundException(
                'Aucun personnage n\'existe en base avec l\'id : ' . $id
            );
        }

        return $this->render('character/read.html.twig', [
            'character' => $character
        ]);
    }

    /**
     * @Route("/character/add", name="character_add")
     */
    public function add(Request $request, FileUploader $fileUploader)
    {
        $character = new Character();

        $form = $this->createForm(CharacterType::class, $character);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $character = $form->getData();

            // upload image file
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageFileName = $fileUploader->upload($imageFile);
                $character->setImageFilename($imageFileName);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($character);
            $entityManager->flush();

            return $this->redirectToRoute('character_read_all');
        }

        return $this->render('character/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/character/edit/id/{id}", name="character_edit")
     */
    public function edit($id, Request $request, FileUploader $fileUploader)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $character = $entityManager->getRepository(Character::class)->find($id);
        $oldImage = $character->getImageFilename();

        if (!$character) {
            throw $this->createNotFoundException(
                'Aucun personnage n\'existe en base avec l\'id : ' . $id
            );
        }

        $form = $this->createForm(CharacterType::class, $character);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $character = $form->getData();

            // upload image file
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                if ($oldImage) {
                    $fileUploader->delete($oldImage);
                }
                $imageFileName = $fileUploader->upload($imageFile);
                $character->setImageFilename($imageFileName);
            }

            $entityManager->persist($character);
            $entityManager->flush();

            return $this->redirectToRoute('character_read_all');
        }

        return $this->render('character/edit.html.twig', [
            'form' => $form->createView(),
            'character' => $character
        ]);
    }

    /**
     * @Route("/character/ajax", name="character_ajax_timeline")
     */
    public function ajax(Request $request)
    {
        $ratio = $request->query->get('ratio');
        $start = $request->query->get('start');

        $repository = $this->getDoctrine()->getRepository(Character::class);

        $characters = $repository->findAll();
        $positions = []; // left css position ratio for each character
        if ($characters) {
            foreach ($characters as $key => $character) {
                $birth = $character->getBirth();
                // if bc true, then set date to negative
                $year = $birth['BC'] ? -1 * $birth['year'] : $birth['year'];
                $left = 0;
                if ($year < 0) {
                    $left = (abs($start) - abs($year)) / $ratio;
                } else {
                    $left = (abs($start) + $year) / $ratio;
                }
                $positions[$key] = $left;
            }
        }

      	return $this->render('character/ajax.html.twig', [
            'characters' => $characters,
            'positions' => $positions,
            'ratio' => $ratio
        ]);

    }
}
