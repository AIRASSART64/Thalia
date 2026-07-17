<?php

namespace App\Form;

use App\Entity\Contact;
use App\Entity\Show;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentOrganization = $options['current_organization'];

        $builder
            ->add('first_name', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Jean'],
                'required'=>true,
            ])
            ->add('last_name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Dupont'],
                'required'=>true,
            ])
            ->add('company_name', TextType::class, [
                'label' => 'Structure / Compagnie',
                'required' => false,
                'attr' => ['placeholder' => 'Compagnie de l\'Instant']
            ])
            ->add('role', TextType::class, [
                'label' => 'Fonction / Rôle',
                'required' => false,
                'attr' => ['placeholder' => 'Chargé de diffusion']
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'required' => false,
            ])
            ->add('shows', EntityType::class, [
                'class'=> Show::class,
                'choice_label'=>'title',
                'multiple'=>true,
                'expanded'=>false,
                'label'=> 'Speactacles rattachés',
                'by_reference'=> false,
                'query_builder' => function (EntityRepository $er) use ($currentOrganization) {
                    return $er->createQueryBuilder('s')
                        ->where('s.organization = :org')
                        ->setParameter('org', $currentOrganization)
                        ->orderBy('s.title', 'ASC');
                },
                'attr' => [
                    'class' => 'w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition outline-none text-gray-800'
                ]
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes ',
                'required' => false,
                'attr' => ['rows' => 2]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
       $resolver->setDefaults([
            'data_class' => Contact::class,
            'current_organization' => null,
        ]);
        $resolver->setRequired('current_organization');
    }
}
