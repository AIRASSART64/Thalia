<?php

namespace App\Form;

use App\Entity\Organization;
use App\Entity\Season;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeasonFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $inputClasses = 'w-full rounded-xl border-slate-200 text-sm focus:border-sky-500 focus:ring-sky-500';

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la saison',
                'attr' => [
                    'placeholder' => 'Ex: Saison 2026-2027',
                    'class' => $inputClasses,
                ],
            ])
            ->add('start_date', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => [
                    'class' => $inputClasses,
                ],
            ])
            ->add('end_date', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => [
                    'class' => $inputClasses,
                ],
            ])
            ->add('is_active', CheckboxType::class, [
                'label' => 'Définir comme saison active',
                'required' => false,
                'attr' => [
                    'class' => 'rounded border-slate-300 text-sky-600 focus:ring-sky-500 h-4 w-4 mr-2',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Season::class,
            'user_organization' => null,
        ]);

        $resolver->setAllowedTypes('user_organization', [Organization::class, 'null']);
    }
    
}
