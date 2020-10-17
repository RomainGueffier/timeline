<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class AgeType extends AbstractType
{
    public function getParent()
    {
        return IntegerType::class;
    }

}
