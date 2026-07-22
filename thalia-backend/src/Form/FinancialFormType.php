<?php

namespace App\Form;

use App\Entity\Financial;
use App\Entity\Organization;
use App\Enum\FinancialCategoryEnum;
// use App\Enum\FinancialTypeEnum;
use App\Enum\VatRateEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
// use Symfony\Component\Form\FormEvent;
// use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FinancialFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Organization|null $organization */
        $organization = $options['user_organization'];

        $builder
            ->add('category', EnumType::class, [
                'class' => FinancialCategoryEnum::class,
                'label' => 'Catégorie financière',
                'placeholder' => 'Sélectionnez une catégorie',
                'choice_label' => fn (FinancialCategoryEnum $choice) => $choice->getLabel(),
                'required' => true,
            ])
            // ->add('type', EnumType::class, [
            //     'class' => FinancialTypeEnum::class,
            //     'label' => 'Type de transaction',
            //     'placeholder' => 'Sélectionnez un type',
            //     'choice_label' => fn ($choice) => ucfirst($choice->value ?? $choice->name),
            //     'required' => true,
            // ])
            ->add('amount_ht', MoneyType::class, [
                'label' => 'Montant HT',
                'currency' => false,
                'scale' => 2,
                'required' => false,
                'attr' => [
                    'placeholder' => '0,00',
                ],
            ])
            ->add('vat_rate', EnumType::class, [
                'class' => VatRateEnum::class,
                'label' => 'Taux de TVA',
                'placeholder' => 'Sélectionnez un taux',
                'choice_label' => fn (VatRateEnum $choice) => method_exists($choice, 'getLabel') ? $choice->getLabel() : ($choice->value ?? $choice->name),
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Précisez l\'objet de cette ligne budgétaire...',
                ],
            ]);

        // // Positionne la valeur de TVA par défaut de l'organisation lors de la création
        // $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($organization) {
        //     /** @var Financial|null $data */
        //     $data = $event->getData();

        //     if ($data && null === $data->getId() && null === $data->getVatRate()) {
        //         if ($organization && method_exists($organization, 'getDefaultVatRate') && $organization->getDefaultVatRate()) {
        //             $data->setVatRate($organization->getDefaultVatRate());
        //         } else {
        //             $data->setVatRate(VatRateEnum::DEFAULT_VAT);
        //         }
        //     }
        // });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Financial::class,
            'user_organization' => null,
        ]);

        $resolver->setAllowedTypes('user_organization', [Organization::class, 'null']);
    }
}