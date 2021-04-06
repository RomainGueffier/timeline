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
     * @Route("/timeline/list", name="timeline_list")
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
        $unit = 100; // en années par 50px
        $range = $timeline->getUnit() ?: 2; // équivalent unit dans le form (2=>100ans,1=>10ans,0=>1an)

        // GET modifications des valeurs
        $range = $request->query->get('range') != null ? $request->query->get('range') : $range;

        // !!! todo vérification date de fin pas inférieure date de début
        $start = $request->query->get('start') != null ? $request->query->get('start') : $start;
        $end = $request->query->get('end') != null ? $request->query->get('end') : $end;

        if ($range == 2) {
            $unit = 100;
        } elseif ($range == 1) {
            $unit = 10;
        } else {
            $unit = 1;
        }
        $ratio = $unit / 50;
        $date = $start;

        $graphic_timeline = '<div class="timeline-period"></div>';
        while ($date < $end) {
            $date += $unit;
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
            'unit' => $unit,
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

            $user = $this->getUser();
            $timeline->setUser($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($timeline);
            $entityManager->flush();

            return $this->redirectToRoute('timeline_list');
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

            return $this->redirectToRoute('timeline_list');
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
    public function delete($id, TranslatorInterface $translator)
    {
        $user = $this->getUser();
        $entityManager = $this->getDoctrine()->getManager();

        $timeline = $entityManager->getRepository(Timeline::class)->findOneBy([
            'id' => $id, 'user' => $user->getId()
        ]);

        if (!$timeline) {
            throw $this->createNotFoundException($translator->trans('pagenotfound'));
        }

        $name = $timeline->getName();

        $entityManager->remove($timeline);
        $entityManager->flush();

        return $this->render('timeline/delete.html.twig', [
            'timeline' => $timeline,
            'name' => $name
        ]);
    }

    /**
     * @Route("/timeline/deleteajax/id/{id}", name="timeline_ajax_delete")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function deleteAjax($id, Response $response): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $timeline = $entityManager->getRepository(Event::class)->findOneBy([
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

        $response->setContent(json_encode([
            'error' => $error,
            'message' => $message
        ]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
