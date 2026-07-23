<?php

namespace App\Form;

use App\Entity\Organization;
use App\Entity\Season;
use App\Enum\SeasonStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeasonFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $inputClasses = 'w-full rounded-xl border-slate-200 text-sm focus:border-sky-500 focus:ring-sky-500';

        $builder
            ->add('season_status', EnumType::class, [
                'class'=> SeasonStatusEnum::class,
                'choice_label'=> fn (SeasonStatusEnum $choice)=> $choice->getLabel(),
                'label'=>"Statut de la saison"
            ])
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
