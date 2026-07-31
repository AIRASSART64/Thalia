<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir votre mot de passe actuel.',
                    ]),
                    new UserPassword([
                        'message' => 'Le mot de passe saisi est incorrect.',
                    ]),
                ],
            ])
            ->add('newPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un nouveau mot de passe.',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre nouveau mot de passe doit faire au minimum {{ limit }} caractères.',
                        'max' => 4096,
                    ]),
                    new Callback([
                        'callback' => function ($newPassword, ExecutionContextInterface $context) {
                            $form = $context->getRoot();
                            $currentPassword = $form->get('currentPassword')->getData();

                            if (!empty($newPassword) && $newPassword === $currentPassword) {
                                $context->buildViolation('Le nouveau mot de passe doit être différent du mot de passe actuel.')
                                    ->addViolation();
                            }
                        },
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}