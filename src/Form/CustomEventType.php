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
            ->add('name', Type\TextType::class)
            ->add('price', Type\NumberType::class)
            ->add('url', Type\UrlType::class)
            ->add('holdingDate', Type\DateType::class)
            ->add('description', Type\TextareaType::class)
            ->add('picture', Type\FileType::class, [
                'required' => false,
            ])
            ->add('submit', Type\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomEvent::class,
        ]);
    }
}
