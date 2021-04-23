<?php

namespace App\Form\Type;

use App\Entity\Event;
use App\Entity\Category;
use App\Entity\Timeline;
use App\Form\Type\OldDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('start', OldDateType::class, ['label' => 'Date de début'])
            ->add('end', OldDateType::class, ['label' => 'Date de fin'])
            ->add('duration', AgeType::class, ['label' => 'Durée'])
            ->add('description', TextareaType::class, ['label' => 'Description'])
            ->add('source', TextType::class, ['label' => 'Sources'])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Merci de téléverser uniquement une image JPG/JPEG',
                    ])
                ]
            ])
            ->add('categories', EntityType::class, [
                'label' => 'Catégorie',
                // looks for choices from this entity
                'class' => Category::class,
                // uses the Category.name property as the visible option string
                'choice_label' => 'name',
                // used to render a select box, check boxes or radios
                'multiple' => true,
                'expanded' => true,
                'choice_attr' => function($category, $key, $value) {
                    // adds a class indicating the timeline ID which own each category
                    return ['class' => 'event-category-timeline-' . $category->getTimeline()->getId()];
                },
            ])
            ->add('timeline', EntityType::class, [
                'label' => 'Frise chronologique',
                'class' => Timeline::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
                'help' => "Sélectionner la frise sur laquelle l'évènement apparaîtra"
            ])
            ->add('save', SubmitType::class, ['label' => 'Sauvegarder']);

            // https://symfony.com/doc/current/form/data_transformers.html#example-1-transforming-strings-form-data-tags-from-user-input-to-an-array
            $builder->get('source')->addModelTransformer(new CallbackTransformer(
                function ($sourceAsArray) {
                    // transform the array to a string
                    return implode(', ', $sourceAsArray);
                },
                function ($sourceAsString) {
                    // transform the string back to an array
                    return explode(', ', $sourceAsString);
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
