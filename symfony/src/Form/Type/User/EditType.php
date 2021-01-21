<?php

namespace App\Form\Type\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class EditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'required' => true,
                'mapped' => false,
                'constraints' => new UserPassword(['message' => 'Mot de passe incorrect'])
            ])
            ->add('email', EmailType::class, ['label' => 'E-mail', 'required' => true])
            ->add('pseudo', TextType::class, ['label' => 'Pseudo', 'required' => true])
            ->add('save', SubmitType::class, ['label' => 'Valider']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => user::class,
        ]);
    }
}
