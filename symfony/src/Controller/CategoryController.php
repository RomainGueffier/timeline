<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Category;
use App\Entity\Timeline;
use App\Form\Type\CategoryType;

class CategoryController extends AbstractController
{
    /**
     * @Route("/category", name="category_read_all")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function index()
    {
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findBy(
                ['user' => $this->getUser()->getId()],
                ['name' => 'ASC']
            );

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/category/read/id/{id}", name="category_read_one")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read($id, TranslatorInterface $translator)
    {
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy([
                'id' => $id,
                'user' => $this->getUser()->getId(),
            ]);

        if (!$category) {
            throw $this->createNotFoundException($translator->trans('pagenotfound'));
        }

        return $this->render('category/read.html.twig', [
            'category' => $category,
            'events' => $category->getEvents(),
            'characters' => $category->getCharacters()
        ]);
    }

    /**
     * @Route("/category/add", name="category_add")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function add(Request $request)
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $category->setUser($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('category_read_all');
        }

        return $this->render('category/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/category/edit/id/{id}", name="category_edit")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function edit($id, Request $request, TranslatorInterface $translator)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category::class)->findOneBy([
            'id' => $id,
            'user' => $this->getUser()->getId(),
        ]);

        if (!$category) {
            throw $this->createNotFoundException($translator->trans('pagenotfound'));
        }

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('category_read_all');
        }

        return $this->render('category/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $category
        ]);
    }

    /**
     * @Route("/category/delete/id/{id}", name="category_delete")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function delete($id, TranslatorInterface $translator)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category::class)->findOneBy([
            'id' => $id,
            'user' => $this->getUser()->getId(),
        ]);
        if (!$category) {
            throw $this->createNotFoundException($translator->trans('pagenotfound'));
        }
        $name = $category->getName();

        if ($category) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->render('category/delete.html.twig', [
            'category' => $category,
            'name' => $name
        ]);
    }

    /**
     * @Route("/category/deleteajax/id/{id}", name="category_ajax_delete")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function deleteAjax($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category::class)->findOneBy([
            'id' => $id,
            'user' => $this->getUser()->getId(),
        ]);
        $message = "Impossible de supprimer cette catégorie";
        $error = true;

        if ($category) {
            $entityManager->remove($category);
            $entityManager->flush();
            $message = "La catégorie " . $category->getName() . " a bien été supprimé";
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
     * @Route("/category/ajax", name="category_ajax_timeline")
     */
    public function ajax(Request $request)
    {
        $timeline_id = $request->query->get('timeline_id');

        $timeline = $this->getDoctrine()->getRepository(Timeline::class)->find($timeline_id);
        $this->denyAccessUnlessGranted('read', $timeline);

        $repository = $this->getDoctrine()->getRepository(Category::class);
        $categories = $repository->findBy([
            'timeline' => $timeline_id
        ]);

      	return $this->render('category/ajax.html.twig', [
            'categories' => $categories
        ]);

    }
}
