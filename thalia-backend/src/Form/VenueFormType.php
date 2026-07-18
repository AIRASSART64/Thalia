<?php

namespace App\Form;

use App\Entity\Equipment;
use App\Entity\Organization;
use App\Entity\Venue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class VenueFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la salle',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de la salle est obligatoire.']),
                ],
            ])
            ->add('maxCapacity', IntegerType::class, [
                'label' => 'Jauge maximale',
                'required' => true,
            ])
            ->add('seatsCount', IntegerType::class, [
                'label' => 'Places assises',
                'required' => false,
            ])
            ->add('standingCount', IntegerType::class, [
                'label' => 'Places debout',
                'required' => false,
            ])
            ->add('pmrCount', IntegerType::class, [
                'label' => 'Places PMR',
                'required' => false,
            ])
            ->add('invitationQuota', IntegerType::class, [
                'label' => 'Quota d\'invitations par défaut',
                'required' => false,
            ])
            ->add('stageWidth', NumberType::class, [
                'label' => 'Largeur scène (m)',
                'scale' => 2,
                'required' => false,
            ])
            ->add('stageDepth', NumberType::class, [
                'label' => 'Profondeur scène (m)',
                'scale' => 2,
                'required' => false,
            ])
            ->add('stageHeight', NumberType::class, [
                'label' => 'Hauteur sous perches (m)',
                'scale' => 2,
                'required' => false,
            ])
            
            // Le champ Image (non mappé directement à l'entité textuelle)
            ->add('venueImageFile', FileType::class, [
                'label' => 'Photo de la salle (JPEG, PNG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, WEBP).',
                    ])
                ],
            ])

            //  Bonus : Le plan technique (ex: PDF ou Image)
            ->add('venuePlanFile', FileType::class, [
                'label' => 'Plan technique (PDF, JPEG, PNG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un document valide (PDF, JPEG, PNG).',
                    ])
                ],
            ])

            // //  Ajout des équipements associés à la salle (ManyToMany)
            // ->add('equipments', EntityType::class, [
            //     'class' => Equipment::class,
            //     'choice_label' => 'name',
            //     'label' => 'Équipements disponibles',
            //     'multiple' => true,
            //     'expanded' => true, 
            //     'required' => false,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Venue::class,
            
            'current_organization' => null, 
        ]);

        
        $resolver->setAllowedTypes('current_organization', [Organization::class, 'null']);
    }
}
