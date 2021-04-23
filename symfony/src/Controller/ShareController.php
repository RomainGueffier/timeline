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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File as ConstraintsFile;
use App\Entity\Timeline;
use App\Entity\Character;
use App\Entity\Event;
use App\Entity\Category;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\ImportUploader;

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
    public function export(Request $request, SerializerInterface $serializer): Response
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
            $exported[$name] = $this->_handleRequest($request, $form, $serializer);
            // build rendering of forms
            $forms[$name . 'Form'] = $form->createView();
        }

        return $this->render('share/export.html.twig', [
            'forms' => $forms,
            'exported' => $exported
        ]);
    }

    /**
     * @Route("/share/import", name="import")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function import(Request $request, SerializerInterface $serializer, ImportUploader $fileUploader): Response
    {
        $form = $this->_generateImportForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $entitiesClasses = [
                'timelines' => Timeline::class,
                'characters' => Character::class,
                'events' => Event::class,
                'categories' => Category::class
            ];
            $entityClass = $entitiesClasses[$data['entity']];
            $timelineId = $data['timeline']; // timeline which children data must be linked

            // upload json file
            $jsonFile = $form->get('file')->getData();
            if ($jsonFile && $entityClass) {
                $filename = $fileUploader->upload($jsonFile);

                // retrieve raw data from json file
                $jsonData = file_get_contents($fileUploader->getTargetDirectory() . '/' . $filename);
                try {
                    $entities = $serializer->deserialize(
                        $jsonData,
                        $entityClass . '[]',
                        'json',
                        [
                            'groups' => [$data['type']] // export or export_all
                        ]
                    );
                } catch (Exception $e) {
                    throw $this->createException("Impossible d'importer ce fichier, les données semblent mal formatée ou le type de donnée ne correspond pas au contenu du fichier : ". $e->getMessage());
                }
                
                //dd($entities);
                // process and storage
                $entityManager = $this->getDoctrine()->getManager();

                foreach ($entities as $entity) {
                    // assign to current user
                    $entity->setUser($this->getUser());
                    if ($data['type'] == 'export_all' && ($entityClass === Timeline::class || $entityClass === Category::class)) {
                        foreach ($entity->getCharacters() as $character) {
                            $character->setUser($this->getUser());
                        }
                        foreach ($entity->getEvents() as $event) {
                            $event->setUser($this->getUser());
                        }
                        if ($entityClass === Timeline::class) {
                            foreach ($entity->getCategories() as $category) {
                                $category->setUser($this->getUser());
                            }
                        }
                    }
                    
                    // import always set visibility private
                    if ($entityClass === Timeline::class) {
                        $entity->setVisibility(false);
                    }

                    // if imported as a child of a database timeline, link to new timeline
                    if ($entityClass !== Timeline::class && $timelineId) {
                        $timeline = $entityManager->getRepository(Timeline::class)->find($timelineId);
                        if ($timeline) {
                            $entity->setTimeline($timeline);
                        }
                    }

                    // save in database
                    $entityManager->persist($entity);
                    $entityManager->flush();
                }

                return $this->render('share/imported.html.twig', [
                    'import_type' => $data['type'],
                    'entity_class' => $data['entity'],
                    'entities' => $entities
                ]);
            }
        } 

        return $this->render('share/import.html.twig', [
            'form' => $form->createView()
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
     * Handle Request and compute data from form to json
     */
    private function _handleRequest(Request $request, object $form, SerializerInterface $serializer)
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
                $formName = $form->getName();
                $exportMode = $form->getClickedButton()->getName();
                $entities = [];

                if (!in_array($formName, array_keys($entitiesClasses))
                    || !$data['entity']
                    || !in_array($exportMode, ['export', 'export_all'])
                ) {
                    return ['error' => true, 'file' => ""];
                }

                // export data
                $json = "";
                $entities = $this->getDoctrine()
                                ->getManager()
                                ->getRepository($entitiesClasses[$formName])
                                ->findBy(['id' => $data['entity']]);

                if ($entities) {
                    $json = $serializer->serialize(
                        $entities,
                        'json',
                        ['groups' => $exportMode] // export or export_all
                    );
                }
                
                return ['error' => false, 'file' => $this->_writeTmpFile($json)];
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
    private function _writeTmpFile(string $data): string
    {
        $filesystem = new Filesystem();
        $filename = 'export' . time();

        try {
            // get tmp folder
            $filesystem->mkdir(sys_get_temp_dir() . '/downloads');
        } catch (IOExceptionInterface $exception) {
            throw $this->createException("Impossible de créer un répertoire dans le dossier temporaire ".$exception->getPath());
        }
   
        $filesystem->dumpFile('/tmp/downloads/' . $filename . '.timeline', $data);

        return $filename;
    }

    /**
     * Import form definition
     */
    private function _generateImportForm()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $timelines = $entityManager->getRepository(Timeline::class)->findAllByUser($this->getUser()->getId());

        return $this->get('form.factory')
            ->createNamed('import')
            ->add('file', FileType::class, [
                'label' => 'Fichier d\'import (.timeline)',
                'required' => true,
                'constraints' => [
                    new ConstraintsFile([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/json',
                        ],
                        'mimeTypesMessage' => 'Merci de téléverser uniquement un fichier .timeline',
                    ])
                ]
            ])
            ->add('entity', ChoiceType::class, [
                'required' => true,
                'label' => 'Type de donnée',
                'help' => 'Sélectionner le type de donnée de contenue dans le fichier',
                'choices' => [
                    'Frises chronologiques' => 'timelines',
                    'Catégories' => 'categories',
                    'Personnages' => 'characters',
                    'Évènements' => 'events'
                ],
                'expanded' => false,
                'multiple' => false,
                'constraints' => [
                    new Choice([
                        'choices' => ['timelines', 'categories', 'characters', 'events'],
                        'message' => "Merci de choisir une option valide !",
                    ])
                ]
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'label' => 'Type d\'import',
                'choices' => [
                    'Import simple' => 'export',
                    'Import complet (avec toutes les données associées)' => 'export_all'
                ],
                'help' => "Un import simple va seulement ajouter à ton compte les données de la frise ou de la catégorie mais pas les personnages ou évènements associés.
                                    Tu devras les associer manuellement. Un export complet ajoute automatiquement les données associées pendant l'import.",
                'constraints' => [
                    new Choice([
                        'choices' => ['export', 'export_all'],
                        'message' => "Merci de choisir une option valide !",
                    ])
                ]
            ])
            ->add('timeline', ChoiceType::class, [
                'placeholder' => '-- Aucune --',
                'required' => false,
                'label' => 'Frise chronologique',
                'choices' => $timelines,
                'help' => "Sélectionne la frise chronologique à laquelle associer les données à importer. Si aucune n'est sélectionnée, les données orphelines pourront toujours être associées plus tard à une frise.",
            ])
            ->add('import', SubmitType::class, ['label' => 'Importer']);
    }
}