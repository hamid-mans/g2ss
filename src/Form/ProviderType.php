<?php

namespace App\Form;

use App\Entity\Provider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProviderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => ['placeholder' => "Nom / Raison sociale"],
            ])
            ->add('email', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Email"],
            ])
            ->add('address1', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Adresse"],
            ])
            ->add('address2', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Complément d'adresse"],
            ])
            ->add('cop', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Code postal"],
            ])
            ->add('city', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Ville"],
            ])
            ->add('phone1', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Téléphone 1"],
            ])
            ->add('phone2', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Téléphone 2"],
            ])
            ->add('address1_liv', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Adresse"],
            ])
            ->add('address2_liv', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Complément d'adresse"],
            ])
            ->add('cop_liv', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Code postal"],
            ])
            ->add('city_liv', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Ville"],
            ])
            ->add('website', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Site web"],
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Description"],
            ])
            ->add('contact_firstname', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Prénom"],
            ])
            ->add('contact_lastname', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Nom"],
            ])
            ->add('contact_phone', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Téléphone"],
            ])
            ->add('contact_email', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => "Email"],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
                'attr' => [
                    'class' => $options['submit_class']
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Provider::class,
            'submit_label' => null,
            'submit_class' => null,
        ]);
    }
}
