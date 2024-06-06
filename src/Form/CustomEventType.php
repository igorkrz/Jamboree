<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\CustomEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, [
                'attr' => [
                    'class' => 'mt-1 p-2 w-full border rounded-md focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300'
                ],
            ])
            ->add('price', Type\NumberType::class, [
                'attr' => [
                    'class' => 'mt-1 p-2 w-full border rounded-md focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300'
                ],
            ])
            ->add('location', Type\TextType::class, [
                'attr' => [
                    'class' => 'mt-1 p-2 w-full border rounded-md focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300'
                ],
            ])
            ->add('holdingDate', Type\DateType::class, [
                'attr' => [
                    'class' => 'mt-1 p-2 w-full border rounded-md focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300'
                ],
                'widget' => 'single_text',
            ])
            ->add('description', Type\TextareaType::class, [
                'attr' => [
                    'class' => 'mt-1 p-2 w-full border rounded-md focus:border-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors duration-300'
                ],
            ])
            ->add('submit', Type\SubmitType::class, [
                'attr' => [
                    'class' => 'w-full bg-black text-white p-2 rounded-md hover:bg-gray-800 focus:outline-none focus:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-colors duration-300',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomEvent::class,
        ]);
    }
}
