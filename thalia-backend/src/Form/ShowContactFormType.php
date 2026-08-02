<?php

namespace App\Form;

use App\Entity\Show;
use App\Entity\ShowContact;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShowContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentOrganization = $options['current_organization'];

        $builder
            ->add('event', EntityType::class, [
                'class' => Show::class,
                'choice_label' => 'title',
                'label' => 'Spectacle',
                'query_builder' => function (EntityRepository $er) use ($currentOrganization) {
                    return $er->createQueryBuilder('s')
                        ->where('s.organization = :org')
                        ->setParameter('org', $currentOrganization)
                        ->orderBy('s.title', 'ASC');
                },
                'attr' => ['class' => 'form-input']
            ])
            ->add('report', TextareaType::class, [
                'label' => 'Compte-rendu / Remarques',
                'required' => false,
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'Notes spécifiques à la relation avec ce spectacle...',
                    'class' => 'form-input'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShowContact::class,
            'current_organization' => null,
        ]);
        $resolver->setRequired('current_organization');
    }
}