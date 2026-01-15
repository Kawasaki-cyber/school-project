<?php

namespace App\Form;

use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\RendezVous;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('patient', EntityType::class, [
                'class' => Patient::class,
                'choice_label' => fn (Patient $p) => sprintf('%s %s (%s)', $p->getFirstName(), $p->getLastName(), $p->getPatientNumber()),
                'placeholder' => '-- Select patient --',
            ])
            ->add('medecin', EntityType::class, [
                'class' => Medecin::class,
                'choice_label' => fn (Medecin $m) => sprintf('%s %s (%s)', $m->getFirstName(), $m->getLastName(), $m->getSpecialization()),
                'placeholder' => '-- Select doctor --',
            ])
            ->add('dateHeure', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Consultation' => 'Consultation',
                    'Follow-up' => 'Follow-up',
                    'Emergency' => 'Emergency',
                    'Check-up' => 'Check-up',
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'programme' => 'programme',
                    'confirme' => 'confirme',
                    'termine' => 'termine',
                    'annule' => 'annule',
                ],
            ])
            ->add('motif', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }
}
