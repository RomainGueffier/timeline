<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Entity\Timeline;

class TimelineFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom', 'required' => true,])
            ->add('description', TextareaType::class, ['label' => 'Description'])
            ->add('start', IntegerType::class, [
                'label' => 'Date de début',
                'help' => 'Date de début de la frise par défaut (mettre un "-" pour les dates avant notre ère)'
            ])
            ->add('end', IntegerType::class, [
                'label' => 'Date de fin',
                'help' => 'Date de fin de la frise par défaut (mettre un "-" pour les dates avant notre ère)'
            ])
            ->add('unit', ChoiceType::class, [
                'required' => true,
                'label' => 'Unité de frise',
                'help' => 'La durée de la plus petite unité de temps sur la courbe',
                'choices' => [
                    '1 an' => 0,
                    '10 ans' => 1,
                    '100 ans' => 2
                ],
                'expanded' => false,
                'multiple' => false
            ])
            ->add('visibility', ChoiceType::class, [
                'required' => true,
                'label' => 'Visibilité',
                'help' => 'Une visibilité publique permettra à tous les utilisateurs de consulter cette frise',
                'choices' => [
                    'Publique' => true,
                    'Privée' => false,
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('save', SubmitType::class, ['label' => 'Sauvegarder']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Timeline::class,
        ]);
    }
}
