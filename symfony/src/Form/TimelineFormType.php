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
use App\Entity\Timeline;

class TimelineFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom', 'required' => true,])
            ->add('description', TextareaType::class, ['label' => 'Description'])
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
