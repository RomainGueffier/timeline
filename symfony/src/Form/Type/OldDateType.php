<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class OldDateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('day', ChoiceType::class, [
                'label' => 'Jour',
                'choices' => array_combine($r = range(1, 31), $r),
            ])
            ->add('month', ChoiceType::class, [
                'label' => 'Mois',
                'choices' => array_combine($r = range(1, 12), $r),
            ])
            ->add('year', IntegerType::class, ['label' => 'Année'])
            ->add('BC', CheckboxType::class, [
                'label' => 'Date avant notre ère',
                'required' => false
            ])
        ;
    }

}
