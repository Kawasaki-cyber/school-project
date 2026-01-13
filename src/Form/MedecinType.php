<?php

namespace App\Form;

use App\Entity\Medecin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MedecinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('license_number', TextType::class, [
                'label' => 'License Number',
            ])
            ->add('first_name', TextType::class, [
                'label' => 'First Name',
            ])
            ->add('last_name', TextType::class, [
                'label' => 'Last Name',
            ])
            ->add('specialization', TextType::class, [
                'label' => 'Specialization',
            ])
            ->add('phone', TextType::class, [
                'label' => 'Phone',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
            ])
            ->add('date_of_birth', DateType::class, [
                'label' => 'Date of Birth',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('license_issue_date', DateType::class, [
                'label' => 'License Issue Date',
                'widget' => 'single_text',
            ])
            ->add('license_expiry_date', DateType::class, [
                'label' => 'License Expiry Date',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'label' => 'Address',
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Medecin::class,
        ]);
    }
}
