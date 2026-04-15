<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Product;
use App\Entity\ProductUnit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MovementsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('productUnits', CollectionType::class, [
            'label' => false,
            'entry_type' => ProductUnitType::class, // Le formulaire de l'unité
            'entry_options' => ['label' => false],
            'allow_add' => true,      // Permet au JS d'ajouter des lignes
            'allow_delete' => true,   // Permet au JS de supprimer des lignes
            'by_reference' => false,  // Obligatoire pour appeler addProductUnit()
            'prototype' => true,      // Génère le modèle HTML pour Twig
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
