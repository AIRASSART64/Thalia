<?php

namespace App\Form;

use App\Entity\Show;
use App\Entity\ShowContact;
use App\Repository\ShowRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShowContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $organization = $options['user_organization'];

        // 1. On ajoute d'abord le champ report
        $builder->add('report', TextareaType::class, [
            'label' => 'Compte-rendu / Remarques (Optionnel)',
            'required' => false,
            'attr' => [
                'rows' => 3,
                'placeholder' => 'Notes spécifiques à ce spectacle pour ce contact...'
            ]
        ]);

        // 2. On intercepte les données au bon moment pour le champ 'event'
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($organization) {
            $form = $event->getForm();
            /** @var ShowContact|null $data */
            $data = $event->getData();

            // L'élément existe en BDD si son ID n'est pas null
            $isExistingLink = $data && $data->getId() !== null;

            $form->add('event', EntityType::class, [
                'class' => Show::class,
                'choice_label' => 'title',
                'query_builder' => function (ShowRepository $er) use ($organization) {
                    return $er->createQueryBuilder('s')
                        ->where('s.organization = :org')
                        ->setParameter('org', $organization)
                        ->orderBy('s.title', 'ASC');
                },
                'placeholder' => 'Sélectionnez un spectacle...',
                'label' => 'Spectacle',
           
                'disabled' => $isExistingLink,
                'attr' => [
                    'class' => 'form-select ' . ($isExistingLink ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : ''),
                ]
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShowContact::class,
            'user_organization' => null,
        ]);
    }
}