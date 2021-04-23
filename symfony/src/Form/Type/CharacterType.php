<?php

namespace App\Form\Type;

use App\Entity\Character;
use App\Entity\Category;
use App\Entity\Timeline;
use App\Form\Type\OldDateType;
use App\Form\Type\AgeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;

class CharacterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('parent')
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('birth', OldDateType::class, ['label' => 'Date de naissance'])
            ->add('birthplace', TextType::class, ['label' => 'Lieu de naissance'])
            ->add('death', OldDateType::class, ['label' => 'Date de décès'])
            ->add('deathplace', TextType::class, ['label' => 'Lieu de décès'])
            ->add('description', TextareaType::class, ['label' => 'Description'])
            ->add('age', AgeType::class, ['label' => 'Durée de vie'])
            ->add('source', TextType::class, ['label' => 'Sources'])
            ->add('weight', IntegerType::class, ['label' => 'Priorité d\'affichage'])
            ->add('image', FileType::class, [
                'label' => 'Image',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
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
                    return ['class' => 'character-category-timeline-' . $category->getTimeline()->getId()];
                },
            ])
            ->add('timeline', EntityType::class, [
                'label' => 'Frise chronologique',
                'class' => Timeline::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
                'help' => "Sélectionner la frise sur laquelle le personnage apparaîtra"
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
            'data_class' => Character::class,
        ]);
    }
}
