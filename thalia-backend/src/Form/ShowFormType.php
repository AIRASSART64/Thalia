<?php

namespace App\Form;

use App\Entity\Show;
use App\Enum\AudienceClassificationEnum;
use App\Enum\DisciplineEnum;
use App\Enum\PipelineStatusEnum;
use App\Service\ThemeTransformerService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ShowFormType extends AbstractType
{
    public function __construct(
        private readonly ThemeTransformerService $themeTransformer
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $organization = $options['user_organization'];
        $this->themeTransformer->setOrganization($organization);

        $builder
            ->add('title', TextType::class, [
                'label' => "Titre du spectacle",
                'required' => true,
            ])
            ->add('discipline', EnumType::class, [
                'class' => DisciplineEnum::class,
                'label' => 'Discipline',
                'required' => true,
            ])
            ->add('audience', EnumType::class, [
                'class' => AudienceClassificationEnum::class,
                'label' => 'Public concerné',
                'required' => true,
            ])
            ->add('duration_min', IntegerType::class, [
                'label' => 'Durée en minute (entracte compris)',
                'required' => true,
            ])
            ->add('synopsis', TextareaType::class, [
                'label' => 'Synopsis du spectacle',
                'required' => true,
            ])

            ->add('themes', TextType::class, [
                'required' => false,
                'label' => 'Thèmes du spectacle',
                'attr' => [
                    'class' => 'tom-select',
                    'placeholder' => 'Choisissez ou tapez un nouveau thème...',
                ],
            ])

            ->add('min_stage_width', NumberType::class, ['label' => 'Largeur scène min.', 'required' => false])
            ->add('min_stage_depth', NumberType::class, ['label' => 'Profondeur scène min.', 'required' => false])
            ->add('min_stage_height', NumberType::class, ['label' => 'Hauteur sous perche min.', 'required' => false])
            ->add('pipeline_status', EnumType::class, [
                'class' => PipelineStatusEnum::class,
                'label' => 'Statut CRM',
                'required' => true,
            ])
            ->add('artworkUrl', FileType::class, [
                'label' => 'Affiche du spectacle (JPG, PNG ou WebP)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '20M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide.',
                    ])
                ],
            ])
            ->add('artistic_file', FileType::class, [
                'label' => 'Documentation (PDF)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '40M',
                        'mimeTypes' => ['application/pdf', 'application/x-pdf'],
                        'mimeTypesMessage' => 'Seuls les documents au format PDF sont acceptés.',
                        'notFoundMessage' => 'Le fichier n\'a pas pu être trouvé sur le serveur.',
                    ])
                ],
            ])
            ->add('artistic_information', TextareaType::class, ['label' => 'Informations artistiques', 'required' => false])
            ->add('technical_information', TextareaType::class, ['label' => 'Informations techniques', 'required' => false])
            ->add('global_unit_cost', MoneyType::class, [
                'label' => 'Coût global unitaire', 
                'currency'=> false,
                'required' => false,
                'attr' => ['placeholder' => '0.00 €']
                ]);

        // Application du DataTransformer
        $builder->get('themes')->addModelTransformer($this->themeTransformer);
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
