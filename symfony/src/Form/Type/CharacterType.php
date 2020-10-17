<?php

namespace App\Form\Type;

use App\Entity\Character;
use App\Form\Type\OldDateType;
use App\Form\Type\AgeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('age', AgeType::class, ['label' => 'Durée de vie'])
            //->add('period')
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
            ->add('save', SubmitType::class, ['label' => 'Sauvegarder']);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Character::class,
        ]);
    }
}
