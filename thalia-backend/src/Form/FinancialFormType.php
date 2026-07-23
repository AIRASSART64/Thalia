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

        /** @var Financial|null $financial */
        $financial = $options['data'] ?? null;
        $season = $financial?->getSeason();

        // Récupère les catégories déjà utilisées pour une sainson donnée
        $usedCategories = [];
        if ($season) {
            foreach ($season->getFinancials() as $existingFinancial) {
                // Ignore la ligne courante en cas de modification
                if ($financial && $existingFinancial->getId() !== $financial->getId()) {
                    $usedCategories[] = $existingFinancial->getCategory();
                }
            }
        }

        $builder
            ->add('category', EnumType::class, [
                'class' => FinancialCategoryEnum::class,
                'label' => 'Catégorie financière',
                'placeholder' => 'Sélectionnez une catégorie',
                'choice_label' => fn (FinancialCategoryEnum $choice) => $choice->getLabel(),
                'required' => true,
                'choice_attr' => function (FinancialCategoryEnum $choice) use ($usedCategories) {
                    if (in_array($choice, $usedCategories, true)) {
                        return [
                            'disabled' => 'disabled',
                            'class' => 'text-slate-300 bg-slate-100',
                        ];
                    }
                    return [];
                },
            ])
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