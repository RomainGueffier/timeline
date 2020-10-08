<?php

namespace App\Form\Type;

use App\Entity\Character;
use App\Form\Type\OldDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

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
            ->add('age', IntegerType::class, ['label' => 'Durée de vie'])
            ->add('accuracy', ChoiceType::class, [
                'label' => 'Exactitude des dates (%)',
                'choices'  => [
                    '100%' => 100,
                    '80%' => 80,
                    '50%' => 50,
                    '0%' => 0,
                ],
            ])
            //->add('period')
            ->add('weight', IntegerType::class, ['label' => 'Priorité d\'affichage'])
            ->add('save', SubmitType::class, ['label' => 'Sauvegarder'])
        ;

        // transform json date into array for form and vice versa
        $builder->get('birth')
            ->addModelTransformer(new CallbackTransformer(
                function ($json) {
                    // transform the json string to an array
                    return json_decode($json, true);
                },
                function ($array) {
                    // transform the array back to json
                    return json_encode($array);
                }
            ))
        ;
        $builder->get('death')
            ->addModelTransformer(new CallbackTransformer(
                function ($json) {
                    // transform the json string to an array
                    return json_decode($json, true);
                },
                function ($array) {
                    // transform the array back to json
                    return json_encode($array);
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Character::class,
        ]);
    }
}
