<?php

namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Character;
use App\Entity\Timeline;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('parent')
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('description', TextareaType::class, ['label' => 'Description'])
            ->add('events', EntityType::class, [
                'label' => 'Évènements',
                'class' => Event::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                // force update even in inverse Side Doctrine ORM Entity
                'by_reference' => false,
                'choice_attr' => function($event, $key, $value) {
                    // adds a class indicating the timeline ID which own each category
                    return ['class' => 'category-timeline-' . $event->getTimeline()->getId()];
                },
            ])
            ->add('characters', EntityType::class, [
                'label' => 'Personnages',
                'class' => Character::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                // force update even in inverse Side Doctrine ORM Entity
                'by_reference' => false,
                'choice_attr' => function($character, $key, $value) {
                    // adds a class indicating the timeline ID which own each category
                    return ['class' => 'category-timeline-' . $character->getTimeline()->getId()];
                },
            ])
            ->add('timeline', EntityType::class, [
                'label' => 'Frise chronologique',
                'class' => Timeline::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
                'help' => "Sélectionner la frise sur laquelle la catégorie apparaîtra"
            ])
            ->add('save', SubmitType::class, ['label' => 'Sauvegarder']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
