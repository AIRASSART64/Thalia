<?php

namespace App\Form;

use App\Entity\Contact;
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
