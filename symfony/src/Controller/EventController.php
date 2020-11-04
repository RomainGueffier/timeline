<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Event;
use App\Form\Type\EventType;
use App\Service\FileUploader;

class EventController extends AbstractController
{
    /**
     * @Route("/event", name="event_read_all")
     */
    public function index()
    {
        $events = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    /**
     * @Route("/event/read/id/{id}", name="event_read_one")
     */
    public function read($id)
    {
        $event = $this->getDoctrine()
            ->getRepository(event::class)
            ->find($id);

        if (!$event) {
            throw $this->createNotFoundException(
                'Aucun évènement n\'existe en base avec l\'id : ' . $id
            );
        }

        return $this->render('event/read.html.twig', [
            'event' => $event
        ]);
    }

    /**
     * @Route("/event/add", name="event_add")
     */
    public function add(Request $request, FileUploader $fileUploader)
    {
        $event = new Event();

        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();

            // upload image file
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageFileName = $fileUploader->upload($imageFile);
                $event->setImageFilename($imageFileName);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_read_all');
        }

        return $this->render('event/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/event/edit/id/{id}", name="event_edit")
     */
    public function edit($id, Request $request, FileUploader $fileUploader)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $event = $entityManager->getRepository(Event::class)->find($id);
        $oldImage = $event->getImageFilename();

        if (!$event) {
            throw $this->createNotFoundException(
                'Aucun évènement n\'existe en base avec l\'id : ' . $id
            );
        }

        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();

            // upload image file
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                if ($oldImage) {
                    $fileUploader->delete($oldImage);
                }
                $imageFileName = $fileUploader->upload($imageFile);
                $event->setImageFilename($imageFileName);
            }

            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_read_all');
        }

        return $this->render('event/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event
        ]);
    }

    /**
     * @Route("/event/delete/id/{id}", name="event_delete")
     */
    public function delete($id, FileUploader $fileUploader)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $event = $entityManager->getRepository(Event::class)->find($id);
        if (!$event) {
            throw $this->createNotFoundException(
                'Aucune évènement n\'existe en base avec l\'id : ' . $id
            );
        }
        $name = $event->getName();

        if ($event) {
            $image = $event->getImageFilename();
            if ($image) {
                $fileUploader->delete($image);
            }
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->render('event/delete.html.twig', [
            'event' => $event,
            'name' => $name
        ]);
    }

    /**
     * @Route("/event/deleteajax/id/{id}", name="event_ajax_delete")
     */
    public function deleteAjax($id, FileUploader $fileUploader)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $event = $entityManager->getRepository(Event::class)->find($id);
        $message = "Aucun évènement n'existe en base avec l'id : " . $id;
        $error = true;

        if ($event) {
            $image = $event->getImageFilename();
            if ($image) {
                $fileUploader->delete($image);
            }
            $entityManager->remove($event);
            $entityManager->flush();
            $message = "L'évènement " . $event->getName() . " a bien été supprimé";
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

    /**
     * @Route("/event/ajax", name="event_ajax_timeline")
     */
    public function ajax(Request $request)
    {
        $ratio = $request->query->get('ratio');
        $timeline_start = $request->query->get('start');
        $timeline_end = $request->query->get('end');

        $repository = $this->getDoctrine()->getRepository(Event::class);

        $events = $repository->findAll();
        $positions = []; // left css position ratio for each event
        if ($events) {
            foreach ($events as $key => $event) {
                $end = $event->getEnd();
                $endyear = $end['BC'] ? -1 * $end['year'] : $end['year'];
                $start = $event->getStart();
                $birthyear = $start['BC'] ? -1 * $start['year'] : $start['year'];
                if ($endyear > $timeline_start && $birthyear < $timeline_end) {
                    $left = 0;
                    if ($birthyear < 0) {
                        $left = (abs($timeline_start) - abs($birthyear)) / $ratio;
                    } else {
                        $left = ($timeline_start >= 0 ? $birthyear - $timeline_start : (abs($timeline_start) + $birthyear)) / $ratio;
                    }
                    $positions[$key] = $left;
                } else {
                    unset($events[$key]);
                }
            }
        }

        return $this->render('event/ajax.html.twig', [
            'events' => $events,
            'positions' => $positions,
            'ratio' => $ratio
        ]);

    }
}
