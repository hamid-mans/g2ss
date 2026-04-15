<?php

// src/Form/MovementSearchType.php
namespace App\Form;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MovementSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Entrée' => 1,
                    'Sortie' => 0,
                ],
                'placeholder' => 'Tous les types',
                'required' => false,
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return $user->getFirstname() . ' ' . strtoupper($user->getLastname());
                },
                'placeholder' => 'Tous les utilisateurs',
                'required' => false,
                'choices' => $options['company_users'],
            ])
            ->add('dateFrom', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'company_users' => [],
        ]);
    }
}