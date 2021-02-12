<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Category;
use App\Form\Type\CategoryType;

class CategoryController extends AbstractController
{
    /**
     * @Route("/category", name="category_read_all")
     */
    public function index()
    {
        $user = $this->getUser();

        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findBy(
                ['user' => $user->getId()],
                ['name' => 'ASC']
            );

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/category/read/id/{id}", name="category_read_one")
     */
    public function read($id)
    {
        $user = $this->getUser();

        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy([
                'id' => $id,
                'user' => $user->getId(),
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
     */
    public function add(Request $request)
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $user = $this->getUser();
            $category->setUser($user->getId());

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
     */
    public function edit($id, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category::class)->find($id);

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
     */
    public function delete($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category::class)->find($id);
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
     */
    public function deleteAjax($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category::class)->find($id);
        $message = "Aucun catégorie n'existe en base avec l'id : " . $id;
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
        $repository = $this->getDoctrine()->getRepository(Category::class);
        $user = $this->getUser();
        $categories = $repository->findBy(
            ['user' => $user->getId()]
        );

      	return $this->render('category/ajax.html.twig', [
            'categories' => $categories
        ]);

    }
}
