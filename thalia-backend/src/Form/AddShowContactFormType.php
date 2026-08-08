<?php

namespace App\Form;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddShowContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $organization = $options['user_organization'];

        // 1. Choix d'un contact existant
        $builder->add('existingContact', EntityType::class, [
            'class' => Contact::class,
            'choice_label' => function (Contact $contact) {
                return sprintf('%s %s (%s)', $contact->getFirstName(), $contact->getLastName(), $contact->getEmail() ?? 'Sans email');
            },
            'query_builder' => function (ContactRepository $cr) use ($organization) {
                return $cr->createQueryBuilder('c')
                    ->where('c.organization = :org')
                    ->setParameter('org', $organization)
                    ->orderBy('c.last_name', 'ASC');
            },
            'placeholder' => '-- Choisir un contact existant --',
            'required' => false,
            'label' => 'Sélectionner un contact existant',
        ]);

        // // 2. Ou Création d'un nouveau contact (Embedded FormType)
        // $builder->add('newContact', ContactFormType::class, [
        //     'user_organization' => $organization,
        //     'required' => false,
        //     'label' => false,
        // ]);

        // 3. Informations spécifiques à la relation (ShowContact)
        // $builder->add('report', TextareaType::class, [
        //     'label' => 'Information sur le contact',
        //     'required' => false,
        //     'attr' => [
        //         'rows' => 3,
        //         'placeholder' => 'Notes ou rôle spécifique du contact sur ce spectacle...',
        //     ],
        // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user_organization' => null,
        ]);
    }
}