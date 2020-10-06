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
        return $this->render('character/index.html.twig', [
            'controller_name' => 'Liste des personnages',
        ]);
    }

    /**
     * @Route("/character/id/{id}", name="wath one character")
     */
    public function read($id)
    {
        return $this->render('character/read.html.twig', []);
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
            $character = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($character);
            // $entityManager->flush();

            return $this->redirectToRoute('character');
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

        $character = $this->getDoctrine()->getRepository(Character::class);

        $characters = $character->findAll();
        if ($characters) {
            foreach ($characters as $key => $values) {
                $left = 0;
                if ($character['birth'] < 0) {
                    $left = (abs($start) - abs($character['birth'])) / $ratio;
                } else {
                    $left = (abs($start) + $character['birth']) / $ratio;
                }
                $characters[$key]['left'] = $left;
            }
        }

      	return $this->render('character/ajax.html.twig', [
            'characters' => $characters,
            'ratio' => $ratio
        ]);

    }
}
