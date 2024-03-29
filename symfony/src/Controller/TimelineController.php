<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Timeline;
use App\Form\TimelineFormType;

class TimelineController extends AbstractController
{

    /**
     * @Route("/timeline/list", name="timeline_read_all")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function list()
    {
        $user = $this->getUser();

        $timelines = $this->getDoctrine()
            ->getRepository(Timeline::class)
            ->findBy(
                ['user' => $user->getId()],
                ['name' => 'ASC']
            );

        return $this->render('timeline/list.html.twig', [
            'timelines' => $timelines,
        ]);
    }

    /**
     * @Route("/timeline/read/id/{id}", name="timeline_read_one")
     */
    public function read($id, Request $request, TranslatorInterface $translator)
    {
        $timeline = $this->getDoctrine()->getRepository(Timeline::class)->find($id);

        if (!$timeline) {
            throw $this->createNotFoundException($translator->trans('pagenotfound'));
        }

        $this->denyAccessUnlessGranted('read', $timeline);

        return $this->render('timeline/read.html.twig', [
            'timeline' => $timeline
        ]);
    }

    /**
     * @Route("/timeline/id/{id}", name="timeline")
     */
    public function index($id, Request $request, TranslatorInterface $translator)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $timeline = $entityManager->getRepository(Timeline::class)->find($id);

        if (!$timeline) {
            throw $this->createNotFoundException($translator->trans('pagenotfound'));
        }

        // Deny access if user is not granted or timeline not public
        $this->denyAccessUnlessGranted('read', $timeline);

        // Valeurs par défaut
        $start = $timeline->getStart() ?: -4100; // année de début de la frise
        $end = $timeline->getEnd() ?: 2000; // année de fin de la frise
        $range = $timeline->getUnit() ?: 100; // en années par 50px (défaut 100 ans)

        // GET modifications des valeurs
        $range = $request->query->get('range') != null ? $request->query->get('range') : $range;
        if ($range < 1) {
            $range = 1;
        }

        // !!! todo vérification date de fin pas inférieure date de début
        $start = $request->query->get('start') != null ? $request->query->get('start') : $start;
        $end = $request->query->get('end') != null ? $request->query->get('end') : $end;

        $ratio = $range / 50;
        $date = $start;

        $graphic_timeline = '<div class="timeline-period"></div>';
        while ($date < $end) {
            $date += $range;
            if ($date == 0) {
                $graphic_timeline .= '<div class="timeline-period timeline-period-zero"><label>0</label></div>';
            } elseif (is_integer(abs($date) / 1000)) {
                $graphic_timeline .= '<div class="timeline-period timeline-period-strong"><label>' . $date . '</label></div>';
            } elseif (is_integer(abs($date - 500) / 1000)) {
                $graphic_timeline .= '<div class="timeline-period timeline-period-light"><label>' . $date . '</label></div>';
            } elseif (is_integer(abs($date) / 100)) {
                $graphic_timeline .= '<div class="timeline-period timeline-period-extralight"><label>' . $date . '</label></div>';
            } else {
                $graphic_timeline .= '<div class="timeline-period"><label>' . $date . '</label></div>';
            }
        }

        return $this->render('timeline/index.html.twig', [
            'ratio' => $ratio,
            'start' => $start,
            'end' => $end,
            'timeline' => $timeline,
            'range' => $range,
            'graphic_timeline' => $graphic_timeline
        ]);
    }

    /**
     * @Route("/timeline/add", name="timeline_add")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function add(Request $request)
    {
        $timeline = new Timeline();

        $form = $this->createForm(TimelineFormType::class, $timeline);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $timeline = $form->getData();

            $timeline->setUser($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($timeline);
            $entityManager->flush();

            return $this->redirectToRoute('timeline_read_all');
        }

        return $this->render('timeline/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/timeline/edit/id/{id}", name="timeline_edit")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function edit($id, Request $request, TranslatorInterface $translator)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $timeline = $entityManager->getRepository(Timeline::class)->findOneBy([
            'id' => $id,
            'user' => $this->getUser()->getId(),
        ]);

        if (!$timeline) {
            throw $this->createNotFoundException($translator->trans('pagenotfound'));
        }

        $form = $this->createForm(TimelineFormType::class, $timeline);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $timeline = $form->getData();

            $entityManager->persist($timeline);
            $entityManager->flush();

            return $this->redirectToRoute('timeline_read_all');
        }

        return $this->render('timeline/edit.html.twig', [
            'form' => $form->createView(),
            'timeline' => $timeline
        ]);
    }

    /**
     * @Route("/timeline/delete/id/{id}", name="timeline_delete")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function delete($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $timeline = $entityManager->getRepository(Timeline::class)->findOneBy([
            'id' => $id,
            'user' => $this->getUser()->getId(),
        ]);
        $message = "Impossible de supprimer cette frise";
        $error = true;

        if ($timeline) {
            $entityManager->remove($timeline);
            $entityManager->flush();
            $message = "La frise " . $timeline->getName() . " a bien été supprimée";
            $error = false;
        }

        $response = new Response();
        $response->setContent(json_encode([
            'error' => $error,
            'message' => $message
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
