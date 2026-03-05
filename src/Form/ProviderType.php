<?php

namespace App\Form;

use App\Entity\Provider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProviderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label = false'
            ])
            ->add('email', TextType::class, [
                'label = false'
            ])
            ->add('address1', TextType::class, [
                'label = false'
            ])
            ->add('address2', TextType::class, [
                'label = false'
            ])
            ->add('cop', TextType::class, [
                'label = false'
            ])
            ->add('city', TextType::class, [
                'label = false'
            ])
            ->add('phone1', TextType::class, [
                'label = false'
            ])
            ->add('phone2', TextType::class, [
                'label = false'
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
                'attr' => [
                    'class' => $options['submit_class'],
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Provider::class,
            'submit_label' => false,
            'submit_class' => null,
        ]);
    }
}
