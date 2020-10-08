<?php

namespace App\Controller;

use App\Entity\Character;
use App\Form\Type\CharacterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CharacterController extends AbstractController
{
    /**
     * @Route("/character", name="wath all character")
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
     * @Route("/character/id/{id}", name="wath one character")
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
     * @Route("/character/add", name="add character")
     */
    public function add(Request $request)
    {
        $character = new Character();

        $form = $this->createForm(CharacterType::class, $character);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            // tell Doctrine you want to (eventually) save the Form (no queries yet)
            $entityManager->persist($data);
            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();

            return $this->redirectToRoute('/character');
        }

        return $this->render('character/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/character/ajax", name="get characters for timeline presentation")
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
                $birth = json_decode($character->getBirth() , true);
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
