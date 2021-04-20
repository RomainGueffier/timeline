<?php

/**
 * @RomainGueffier
 * Controller for import/export of data and others share routines
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Choice;
use App\Entity\Timeline;
use App\Entity\Character;
use App\Entity\Event;
use App\Entity\Category;

class ShareController extends AbstractController
{
    // https://symfony.com/blog/new-in-symfony-5-2-php-8-attributes
    #[Route('/share', name: 'share')]
    public function index(): Response
    {
        return $this->render('share/index.html.twig', [
            'controller_name' => 'ShareController',
        ]);
    }

    /**
     * @Route("/share/export", name="export")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function export(Request $request): Response
    {
        $userId = $this->getUser()->getId();
        
        $entityManager = $this->getDoctrine()->getManager();
        $entities = [
            'timelines' => $entityManager->getRepository(Timeline::class)->findAllByUser($userId),
            'characters' => $entityManager->getRepository(Character::class)->findAllByUser($userId),
            'categories' => $entityManager->getRepository(Category::class)->findAllByUser($userId),
            'events' => $entityManager->getRepository(Event::class)->findAllByUser($userId)
        ];
        $forms = [];
        $exported = [];

        foreach ($entities as $name => $data) {
            // build form object from data and name
            $form = $this->_generateEntityListForm($name, $data);
            // catch POST request to compute posted form
            $exported[$name] = $this->_handleRequest($request, $form);
            // build rendering of forms
            $forms[$name . 'Form'] = $form->createView();
        }

        return $this->render('share/export.html.twig', [
            'forms' => $forms,
            'exported' => $exported
        ]);
    }

    #[Route('/share/import', name: 'import')]
    public function import(): Response
    {
        return $this->render('share/import.html.twig', [
        ]);
    }

    #[Route('/share/download/file/{file}', name: 'download')]
    public function download(string $file): Response
    {
        $file = new File('/tmp/downloads/' . $file . '.timeline');
        if ($file) {
            // send the file contents and force the browser to download it
            return $this->file($file);
        }
        // else return error on link
        throw $this->createNotFoundException($translator->trans('pagenotfound'));
    }

    /**
     * Generate a form from an entity name and a array containing a pair of id and name
     * @return Object $form
     */
    private function _generateEntityListForm(string $name, array $data): object
    {
        // defining custom form here because it's not linked with a entity
        $labels = [
            'timelines' => 'Frises chronologiques',
            'characters' => 'Personnages',
            'events' => 'Évènements',
            'categories' => 'Catégories'
        ];

        $form = $this->get('form.factory')
            ->createNamed($name)
            ->add('entity', ChoiceType::class, [
                'required' => true,
                'choices' => $data,
                'label' => $labels[$name],
                'expanded' => true,
                'multiple' => true,
                'constraints' => [
                    new NotBlank(),
                    new Choice([
                        'choices' => array_values($data),
                        'message' => "Merci de choisir une option valide !",
                        'multipleMessage' => "Merci de choisir une option valide !",
                        'multiple' => true
                    ])
                ]
            ])
            ->add('export', SubmitType::class, ['label' => 'Exporter']);
        
        if ($name === 'timelines' || $name === 'categories') {
            $form->add('export_all', SubmitType::class, ['label' => 'Exporter avec toutes les données associées']);
        }

        return $form;
    }

    /**
     * Handle Request and compute data from form
     */
    private function _handleRequest(Request $request, object $form)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();

                $entitiesClasses = [
                    'timelines' => Timeline::class,
                    'characters' => Character::class,
                    'events' => Event::class,
                    'categories' => Category::class
                ];

                if (!in_array($form->getName(), array_keys($entitiesClasses))) {
                    return ['error' => true, 'file' => ""];
                }

                // export data
                $entityManager = $this->getDoctrine()->getManager();
                // get main entity's data
                $entities = $entityManager->getRepository($entitiesClasses[$form->getName()])->exportByIds($data['entity']);
                // if button all data clicked, then add linked data to main entity
                if ($form->has('export_all') && $form->getClickedButton() === $form->get('export_all')) {
                    //$childrenEntities = $entityManager->getRepository(Character::class)->exportByIds($data['entity']);
                }

                //dd(json_encode($entities));
                return ['error' => false, 'file' => $this->_storeInJsonFile($entities)];
            }
            // form submitted, but errors
            return ['error' => true, 'file' => ""];
        }
        // form not submitted, no error
        return [];
    }

    /**
     * @return string $filepath
     * Store json data in tmp file with timeline extension to show
     * dependency of data with current app
     * https://symfony.com/doc/current/components/filesystem.html
     */
    private function _storeInJsonFile(array $data): string
    {
        $filesystem = new Filesystem();
        $filename = 'export' . time();

        try {
            // get tmp folder
            $filesystem->mkdir(sys_get_temp_dir() . '/downloads');
        } catch (IOExceptionInterface $exception) {
            throw $this->createException("Impossible de créer un répertoire dans le dossier temporaire ".$exception->getPath());
        }
   
        $filesystem->dumpFile('/tmp/downloads/' . $filename . '.timeline', json_encode($data));

        return $filename;
    }
}
