<?php

namespace App\Form;

use App\Entity\Contact;
use App\Entity\Organization;
use App\Entity\Show;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShowFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('discipline')
            ->add('duration_min')
            ->add('synopsis')
            ->add('min_stage_width')
            ->add('min_stage_depth')
            ->add('min_stage_height')
            ->add('pipeline_status')
            ->add('artwork_url')
            ->add('created_at', null, [
                'widget' => 'single_text',
            ])
            ->add('updated_at', null, [
                'widget' => 'single_text',
            ])
            ->add('organization', EntityType::class, [
                'class' => Organization::class,
                'choice_label' => 'id',
            ])
            ->add('contacts', EntityType::class, [
                'class' => Contact::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Show::class,
        ]);
    }
}
