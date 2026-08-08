<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
        $currentOrganization = $options['user_organization'];


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
        
            ->add('showContacts', CollectionType::class, [
                'entry_type' => ShowContactFormType::class,
                'entry_options' => [
                    'user_organization' => $currentOrganization,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false, 
                'label' => false,
            ])

            ->add('notes', TextareaType::class, [
                'label' => 'Concernant le contact ',
                'required' => false,
                'attr' => ['rows' => 2]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
       $resolver->setDefaults([
            'data_class' => Contact::class,
            'user_organization' => null,
        ]);
        $resolver->setRequired('user_organization');
    }
}
