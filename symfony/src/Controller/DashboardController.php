<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="dashboard")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/dashboard/home', name: 'dashboard_home')]
    public function home(): Response
    {
        return $this->render('dashboard/home.html.twig');
    }
}
