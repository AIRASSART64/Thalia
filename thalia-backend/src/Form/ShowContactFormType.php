<?php

namespace App\Form;

use App\Entity\Contact;
use App\Entity\Show;
use App\Entity\ShowContact;
use App\Repository\ContactRepository;
use App\Repository\ShowRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShowContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $organization = $options['user_organization'];
        $show = $options['show'];

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($organization, $show) {
            $form = $event->getForm();
            /** @var ShowContact|null $data */
            $data = $event->getData();

            $isExistingLink = $data && $data->getId() !== null;
            $contact = $data ? $data->getContact() : null;

            if (!$show) {
                $form->add('event', EntityType::class, [
                    'class' => Show::class,
                    'choice_label' => 'title',
                    'placeholder' => 'Sélectionnez un spectacle...',
                    'label' => 'Spectacle',
                    'disabled' => $isExistingLink,
                    'required' => true,
                    // Exclusion des spectacles auxquels ce contact est DÉJÀ rattaché
                    'query_builder' => function (ShowRepository $sr) use ($organization, $contact, $isExistingLink) {
                        $qb = $sr->createQueryBuilder('s')
                            ->where('s.organization = :org')
                            ->setParameter('org', $organization);

                        // contact existant pour un nouveau rattachement
                        if ($contact && $contact->getId() !== null && !$isExistingLink) {
                            $qb->andWhere(
                                $qb->expr()->notIn('s.id',
                                    $sr->createQueryBuilder('sub_s')
                                        ->select('s2.id')
                                        ->from(ShowContact::class, 'sc')
                                        ->join('sc.event', 's2') // 
                                        ->where('sc.contact = :targetContact')
                                        ->getDQL()
                                )
                            )
                            ->setParameter('targetContact', $contact);
                        }

                        return $qb->orderBy('s.title', 'ASC');
                    },
                    'attr' => [
                        'class' => 'form-select ' . ($isExistingLink ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : ''),
                    ]
                ]);
            }

            // 2. CHAMP CONTACT (Masqué lors de la création d'un tout nouveau contact)
            if (!($contact && $contact->getId() === null)) {
                $form->add('contact', EntityType::class, [
                    'class' => Contact::class,
                    'choice_label' => function (Contact $c) {
                        $fullname = trim($c->getFirstName() . ' ' . $c->getLastName());
                        return $fullname ?: $c->getEmail();
                    },
                    'placeholder' => 'Sélectionnez un contact...',
                    'label' => 'Contact',
                    'disabled' => $isExistingLink,
                    'required' => true,
                    // Exclusion des contacts déjà rattachés à un spectacle
                    'query_builder' => function (ContactRepository $cr) use ($organization, $show, $isExistingLink) {
                        $qb = $cr->createQueryBuilder('c')
                            ->where('c.organization = :org')
                            ->setParameter('org', $organization);

                        //  un spectacle et qu'on crée un nouveau rattachement
                        if ($show && !$isExistingLink) {
                            $qb->andWhere(
                                $qb->expr()->notIn('c.id', 
                                    $cr->createQueryBuilder('sub_c')
                                        ->select('c2.id')
                                        ->from(ShowContact::class, 'sc')
                                        ->join('sc.contact', 'c2')
                                        ->where('sc.event = :targetShow') 
                                        ->getDQL()
                                )
                            )
                            ->setParameter('targetShow', $show);
                        }

                        return $qb->orderBy('c.last_name', 'ASC')
                                 ->addOrderBy('c.first_name', 'ASC');
                    },
                    'attr' => [
                        'class' => 'form-select ' . ($isExistingLink ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : ''),
                    ]
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShowContact::class,
            'user_organization' => null,
            'show' => null, 
        ]);

        $resolver->addAllowedTypes('show', [Show::class, 'null']);
    }
}