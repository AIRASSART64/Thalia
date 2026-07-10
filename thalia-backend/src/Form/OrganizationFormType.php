<?php

namespace App\Form;

use App\Entity\Organization;
use App\Enum\ErpCategory;
use App\Enum\ErpType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class OrganizationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('siret', TextType::class, ['label'=>'Numéro de SIRET',
            'attr' => [
                    'maxlength' => 14, 
                    'placeholder' => '12345678901234',
                    ],
                'constraints' => [
                    new Length([
                        'min' => 14,
                        'max' => 14,
                        'exactMessage' => 'Le numéro SIRET doit comporter exactement {{ limit }} chiffres.',
                        ]),],
                 ])
            ->add('name', TextType::class, ['label'=>"Nom de l'établissement"])
            ->add('licence_number', TextType::class, ['label'=> "N° d'entrepreneur de spectacle", 'required'=> false])
            ->add('adress', TextareaType::class, [ 'label'=> "Adresse de l'établissement", 'required'=>false])
            ->add('business_name', TextType::class, ['label'=>'Raison sociale', 'required'=>false])
            ->add('legal_status', TextType::class, [ 'label'=>'Statut juridique', 'required'=>false])
            ->add('phone', TelType::class, ['label'=> "Numéro de telephone", 'required'=>false]) 
            ->add('manager_name', TextType::class ,['label'=>'Nom du responsable légal', 'required'=>false])
            ->add('manager_title', TextType::class, ['label'=>'Titre du responsable légale', 'required'=>false])
            ->add('vat_number', TextType::class, [ 'label'=>"Numéro de TVA intracommunautaire", 'required'=>false])
            ->add('defaultVatRate', TextType::class, ['label'=> 'Taux TVA standard', 'required'=>false])
            ->add('spectacle_vat_rate', TextType::class, ['label'=> 'Taux TVA spectacle', 'required'=>false])
            ->add('erp_category', EnumType::class, [
                    'class'=>ErpCategory::class,
                    'label'=>"Catégorie d'ERP",
                    'required'=>false
            ])
            ->add('erp_type', EnumType::class, [
                     'class'=>ErpType::class,
                    'label'=>"Type d'ERP",
                    'required'=>false
            ])
           

         
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Organization::class,
        ]);
    }
}
