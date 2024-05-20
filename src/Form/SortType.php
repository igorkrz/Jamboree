<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SortType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('sort', Type\ChoiceType::class, [
                'placeholder' => 'Sort by',
                'choices' => [
                    'Date' => 'getHoldingDate',
                    'Price' => 'getPrice',
                    'A-Z' => 'getName'
                ]
            ]);
    }
}
