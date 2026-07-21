<?php

namespace App\Form;

use App\Entity\Equipment;
use App\Entity\Organization;
use App\Entity\Venue;
use App\Enum\EquipmentCategoyEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label'=>"Nom de l'équipement",
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Enceinte L-Acoustics, Console Soundcraft...',
                ],
            ])
            ->add('category', EnumType::class, [
                'class' => EquipmentCategoyEnum::class,
                'label' => 'Catégorie(s)',
                'multiple' => false,      
                'required' => false,
            ] )
            ->add('total_quantity', IntegerType::class, [
                'label' => 'Quantité totale',
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'placeholder' => '1',
                ],
            ])
            ->add('venue', EntityType::class, [
                'class' => Venue::class,
                'choice_label' => 'name', 
                'label' => 'Lieu / Salle associée',
                'placeholder' => '-- Aucun lieu (Stock général) --',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipment::class,
            'user_organization' => null,
        ]);
        $resolver->setAllowedTypes('user_organization', [Organization::class, 'null']);
    }
}
