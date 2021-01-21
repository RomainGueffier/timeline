<?php

namespace App\Form\Type\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'required' => true,
                'constraints' => new UserPassword(['message' => 'Mot de passe incorrect'])
            ])
            ->add('newpassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'mapped' => false,
                'first_options'  => [
                    'label' => 'Nouveau mot de passe',
                    'help' => 'Le changement de mot de passe sera effectif à la prochaine connexion',
                ],
                'second_options' => ['label' => 'Confirmation du mot de passe'],
                'constraints' => new Length([
                     'min' => 6,
                     'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                     // max length allowed by Symfony for security reasons
                     'max' => 4096,
                ])
            ])
            ->add('save', SubmitType::class, ['label' => 'Valider']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
