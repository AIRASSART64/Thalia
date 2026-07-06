<?php

namespace App\Form;


use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
               ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [new NotBlank(['message' => 'Veuillez saisir votre prénom.'])]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'constraints' => [new NotBlank(['message' => 'Veuillez saisir votre nom.'])]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email professionnelle',
                'constraints' => [new NotBlank(['message' => 'Veuillez saisir une adresse email.'])]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un mot de passe.']),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit faire au moins 8 caractères.',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('siret', TextType::class, [
                'label' => 'Numéro SIRET de l’établissement',
                'mapped' => false,
                'attr' => ['placeholder' => '14 chiffres (ex: 12345678912345)'],
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro SIRET est obligatoire pour identifier votre structure.']),
                    new Length([
                        'min' => 14,
                        'max' => 14,
                        'exactMessage' => 'Un numéro SIRET valide comporte exactement {{ limit }} chiffres.'
                    ]),
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
