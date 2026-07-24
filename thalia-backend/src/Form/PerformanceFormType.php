<?php

namespace App\Form;

use App\Entity\Organization;
use App\Entity\Performance;
use App\Entity\Season;
use App\Entity\Show;
use App\Entity\Venue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PerformanceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
          
            ->add('date_time_start', DateTimeType::class, [
                'label' => 'Début de la représentation',
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['class' => 'rounded-xl border-gray-200'],
            ])
            ->add('date_time_end', DateTimeType::class, [
                'label' => 'Fin de la représentation',
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['class' => 'rounded-xl border-gray-200'],
            ])
       
            ->add('setup_duration_min', IntegerType::class, [
                'label' => 'Temps de montage (min)',
                'required' => false,
                'attr' => ['placeholder' => 'ex: 120', 'min' => 0],
            ])
            ->add('teardown_duration_min', IntegerType::class, [
                'label' => 'Temps de démontage (min)',
                'required' => false,
                'attr' => ['placeholder' => 'ex: 60', 'min' => 0],
            ])

            ->add('ticket_price_standard', MoneyType::class, [
                'label' => 'Prix du billet (Plein tarif)',
                'currency' => 'EUR',
                'required' => false,
                'attr' => ['placeholder' => '0.00'],
            ])
            ->add('ticket_price_reduced', MoneyType::class, [
                'label' => 'Prix du billet (Tarif réduit)',
                'currency' => 'EUR',
                'required' => false,
                'attr' => ['placeholder' => '0.00'],
            ])
            ->add('estimated_attendance_percent', IntegerType::class, [
                'label' => 'Fréquentation estimée (%)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'ex: 85',
                    'min' => 0,
                    'max' => 100,
                ],
            ])

            ->add('total_cost', MoneyType::class, [
                'label' => 'Coût total de la prestation',
                'currency' => 'EUR',
                'required' => false,
                'attr' => ['placeholder' => '0.00'],
            ])

            ->add('season_show', EntityType::class, [
                'class' => Show::class,
                'label' => 'Spectacle associé',
                'choice_label' => 'title', // Affiche le titre du spectacle
                'placeholder' => 'Sélectionner un spectacle...',
                'required' => true,
            ])
            ->add('venue', EntityType::class, [
                'class' => Venue::class,
                'label' => 'Lieu de représentation',
                'choice_label' => 'name', // Affiche le nom de la salle/lieu
                'placeholder' => 'Sélectionner un lieu...',
                'required' => false,
            ])
            ->add('season', EntityType::class, [
                'class' => Season::class,
                'label' => 'Saison culturelle',
                'choice_label' => 'name', // Affiche le nom de la saison
                'placeholder' => 'Sélectionner une saison...',
                'required' => false,
            ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Performance::class,
            'user_organization' => null,
        ]);
     $resolver->setAllowedTypes('user_organization', [Organization::class, 'null']);
    }
}