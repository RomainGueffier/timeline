<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Character;
use App\Form\Type\CharacterType;
use App\Service\FileUploader;

class CharacterController extends AbstractController
{
    /**
     * @Route("/character", name="character_read_all")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function index(): Response
    {
        $characters = $this->getDoctrine()
            ->getRepository(Character::class)
            ->findBy(
                ['user' => $this->getUser()->getId()],
                ['name' => 'ASC']
            );

        return $this->render('character/index.html.twig', [
            'characters' => $characters,
        ]);
    }

    /**
     * @Route("/character/read/id/{id}", name="character_read_one")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read($id, TranslatorInterface $translator): Response
    {
        $character = $this->getDoctrine()
            ->getRepository(Character::class)
            ->findOneBy([
                'id' => $id,
                'user' => $this->getUser()->getId(),
            ]);

        if (!$character) {
            throw $this->createNotFoundException($translator->trans('pagenotfound'));
        }

        return $this->render('character/read.html.twig', [
            'character' => $character
        ]);
    }

    /**
     * @Route("/character/add", name="character_add")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function add(Request $request, FileUploader $fileUploader): Response
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

            $character->setUser($this->getUser());

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
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function edit($id, Request $request, FileUploader $fileUploader, TranslatorInterface $translator)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $character = $entityManager->getRepository(Character::class)->findOneBy([
            'id' => $id,
            'user' => $this->getUser()->getId(),
        ]);

        if (!$character) {
            throw $this->createNotFoundException($translator->trans('pagenotfound'));
        }

        $oldImage = $character->getImageFilename();
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
     * @Route("/character/delete/id/{id}", name="character_delete")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function delete($id, FileUploader $fileUploader, TranslatorInterface $translator)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $character = $entityManager->getRepository(Character::class)->findOneBy([
            'id' => $id,
            'user' => $this->getUser()->getId(),
        ]);

        if (!$character) {
            throw $this->createNotFoundException($translator->trans('pagenotfound'));
        }
        $name = $character->getName();

        if ($character) {
            $image = $character->getImageFilename();
            if ($image) {
                $fileUploader->delete($image);
            }
            $entityManager->remove($character);
            $entityManager->flush();
        }

        return $this->render('character/delete.html.twig', [
            'character' => $character,
            'name' => $name
        ]);
    }

    /**
     * @Route("/character/deleteajax/id/{id}", name="character_ajax_delete")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function deleteAjax($id, FileUploader $fileUploader, Response $response): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $character = $entityManager->getRepository(Character::class)->findOneBy([
            'id' => $id,
            'user' => $this->getUser()->getId(),
        ]);
        $message = "Impossible de supprimer ce personnage";
        $error = true;

        if ($character) {
            $image = $character->getImageFilename();
            if ($image) {
                $fileUploader->delete($image);
            }
            $entityManager->remove($character);
            $entityManager->flush();
            $message = "Le personnage " . $character->getName() . " a bien été supprimé";
            $error = false;
        }

        $response->setContent(json_encode([
            'error' => $error,
            'message' => $message
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/character/ajax", name="character_ajax_timeline")
     */
    public function ajax(Request $request)
    {
        $ratio = $request->query->get('ratio');
        $start = $request->query->get('start');
        $end = $request->query->get('end');
        $timeline_id = $request->query->get('timeline_id');

        $repository = $this->getDoctrine()->getRepository(Character::class);

        $characters = $repository->findBy(
            ['timeline' => $timeline_id]
        );
        $positions = []; // left css position ratio for each character
        if ($characters) {
            foreach ($characters as $key => $character) {
                $death = $character->getDeath();
                // if bc true, then set date to negative
                $deathyear = $death['BC'] ? -1 * $death['year'] : $death['year'];
                $birth = $character->getBirth();
                // if bc true, then set date to negative
                $birthyear = $birth['BC'] ? -1 * $birth['year'] : $birth['year'];
                if ($deathyear > $start && $birthyear < $end) {
                    $left = 0;
                    if ($birthyear < 0) {
                        $left = (abs($start) - abs($birthyear)) / $ratio;
                    } else {
                        $left = ($start >= 0 ? $birthyear - $start : (abs($start) + $birthyear)) / $ratio;
                    }
                    $positions[$key] = $left;
                } else {
                    unset($characters[$key]);
                }
            }
        }

      	return $this->render('character/ajax.html.twig', [
            'characters' => $characters,
            'positions' => $positions,
            'ratio' => $ratio
        ]);

    }
}
