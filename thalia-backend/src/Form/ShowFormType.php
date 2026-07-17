<?php

namespace App\Form;

use App\Entity\Show;
use App\Enum\DisciplineEnum;
use App\Enum\PipelineStatusEnum;
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
        $organization = $options['current_organization'];
        $builder
            ->add('title' , TextType::class, [
                'label'=>"Titre du spectacle",
                'required' => true,])
            ->add('discipline', EnumType::class, [
                'class'=> DisciplineEnum::class,
                'label'=> 'Discipline',
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Show::class,
            'current_organization' => null,
        ]);
        $resolver->setRequired('current_organization');
    }
}
