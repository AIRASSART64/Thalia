<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContactSupportFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez renseigner votre nom.']),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir votre adresse e-mail.']),
                    new Assert\Email(['message' => 'L\'adresse e-mail saisie n\'est pas valide.']),
                ],
            ])
            ->add('subject', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choisissez un sujet...',
                'choices' => [
                    'Problème d\'accès / Connexion' => 'acces',
                    'Signalement de défaut d\'accessibilité (RGAA)' => 'rgaa',
                    'Question sur la gestion des données (RGPD)' => 'rgpd',
                    'Demande de suppresion du compte' => 'suppression compte',
                    'Demande de récupération des données personnelles' => 'données personnelles',
                    'Autre demande' => 'autre',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner un sujet.']),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez rédiger votre message.']),
                    new Assert\Length(['min' => 10, 'minMessage' => 'Votre message doit contenir au moins 10 caractères.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}