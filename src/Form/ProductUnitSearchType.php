<?php

// src/Form/ProductUnitSearchType.php
namespace App\Form;

use App\Entity\Deposit;
use App\Repository\DepositRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductUnitSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $company = $options['company'];

        $builder
            ->add('q', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Rechercher par n° de série...']
            ])
            ->add('deposit', EntityType::class, [
                'class' => Deposit::class,
                'choice_label' => 'name',
                'label' => false,
                'required' => false,
                'placeholder' => 'Tous les dépôts',
                'choices' => $options['company_deposits']
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'company_deposits' => [],
            'company' => [],
        ]);
    }
}