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
            ])
            ->add('email', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('address1', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('address2', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('cop', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('phone1', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('phone2', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('address1_liv', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('address2_liv', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('cop_liv', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('city_liv', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('website', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('contact_firstname', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('contact_lastname', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('contact_phone', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('contact_email', TextType::class, [
                'label' => false,
                'required' => false,
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
