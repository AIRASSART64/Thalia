<?php

namespace App\Form;

use App\Entity\Show;
use App\Entity\Theme;
use App\Enum\AudienceClassificationEnum;
use App\Enum\DisciplineEnum;
use App\Enum\PipelineStatusEnum;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ShowFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $organization = $options['user_organization'];
        $builder
            ->add('title' , TextType::class, [
                'label'=>"Titre du spectacle",
                'required' => true,])
            ->add('discipline', EnumType::class, [
                'class'=> DisciplineEnum::class,
                'label'=> 'Discipline',
                'required'=>true,
            ])
              ->add('audience', EnumType::class, [
                'class'=> AudienceClassificationEnum::class,
                'label'=> 'Public concerné',
                'required'=>true,
            ])
            ->add('duration_min', IntegerType::class, [
                'label'=>'Durée en minute (entracte compris)',
                'required' => true,
            ])
            ->add('synopsis', TextareaType::class, [
                'label'=>'Synopsis du spectacle',
                'required' => true,
            ])
            ->add('themes', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'label' => 'Thèmes du spectacle',
                'attr' => [
                    'class' => 'tom-select',
                    'placeholder' => 'Choisissez ou tapez un nouveau thème...',
                ],
                'query_builder' => function (EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('t')
                        ->where('t.organization = :org')
                        ->setParameter('org', $organization)
                        ->orderBy('t.name', 'ASC');
                },
                
            ])
            ->add('min_stage_width', NumberType::class, [
                'label' => 'Largeur scène min.',
                'required' => false,
            ])
            ->add('min_stage_depth', NumberType::class, [
                'label' => 'Profonder scène min.',
                'required' => false,
             ])
            
            ->add('min_stage_height', NumberType::class, [
                'label' => 'hauteur sous perche min.',
                'required' => false,
             ])
            ->add('pipeline_status', EnumType::class ,[
                'class' => PipelineStatusEnum::class,
                'label'=> 'Statut CRM',
                'required'=>true,
            ])
            ->add('artworkUrl', FileType::class, [
                'label' => 'Affiche du spectacle (JPG, PNG ou WebP)',
                'mapped' => false, 
                'required' => false,
                'constraints' => [
                new File([
                'maxSize' => '1024k',
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                'mimeTypesMessage' => 'Veuillez uploader une image valide.',
                         ])
                    ],
                ])
            ->add('artistic_file', FileType::class, [
                'label' => 'Documentation  (PDF)',
                'mapped' => false, 
                'required' => false,
                'constraints' => [
                new File([
                'maxSize' => '4000k',
                'mimeTypes' => ['application/pdf','application/x-pdf',],
                'mimeTypesMessage' => 'Seuls les docments au format PDF sont accéptés',
                         ])
                    ],
                ])
            ->add('artistic_information', TextareaType::class, [
                'label'=>'Informations artistiques',
                'required' => false,
                'attr' => [
                        'placeholder' => 'Nombre de comediens, détail mise en scéne, décors, costumes ...'
                        ],
            ])
               ->add('technical_information', TextareaType::class, [
                'label'=>'Informations techniques',
                'required' => false,
                'attr' => [
                        'placeholder' => 'Temps de montage et démontage, équipement requis, ...'
                        ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Show::class,
            'user_organization' => null,
        ]);
        $resolver->setRequired('user_organization');
    }
}
