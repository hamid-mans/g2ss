<?php

namespace App\Form;

use App\Entity\Provider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProviderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('email')
            ->add('address1')
            ->add('address2')
            ->add('cop')
            ->add('city')
            ->add('phone1')
            ->add('phone2')
            ->add('address1�_liv')
            ->add('address2_liv')
            ->add('cop_liv')
            ->add('city_liv')
            ->add('website')
            ->add('description')
            ->add('contact_firstname')
            ->add('contact_lastname')
            ->add('contact_phone')
            ->add('contact_email')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Provider::class,
        ]);
    }
}
