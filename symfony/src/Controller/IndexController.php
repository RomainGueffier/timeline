<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Timeline;

class IndexController extends AbstractController
{
    /**
     * @Route("/index", name="index")
     */
    public function index()
    {
        $timelines = $this->getDoctrine()
            ->getRepository(Timeline::class)
            ->findBy(
                ['visibility' => true],
                ['name' => 'ASC']
            );

        return $this->render('index/index.html.twig', [
            'timelines' => $timelines,
        ]);
    }
}
