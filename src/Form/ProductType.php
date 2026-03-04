<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('designation', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('refInterne', TextType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank([], 'La référence interne est obligatoire.'),
                ],
                'error_bubbling' => true
            ])
            ->add('refSupplier', TextType::class, [
                'label' => false,
                'required' => false,
                /*'constraints' => [
                    new NotBlank([], 'La référence fournisseur est obligatoire.')
                ],
                'error_bubbling' => true,*/
            ])
            ->add('sellPrice', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('brands', EntityType::class, [
                'label' => false,
                'class' => Brand::class,
                'required' => false,
                'placeholder' => "Aucune marque",
                'choice_label' => 'label',
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
                'attr' => [
                    'class' => $options['submit_class']
                ]
            ])
            ->add('category', EntityType::class, [
                'label' => false,
                'class' => Category::class,
                'required' => false,
                'placeholder' => "Aucune catégorie",
                'choice_label' => 'label',
                'attr' => [
                    'class' => 'ui dropdown search'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'submit_label' => false,
            'submit_class' => null
        ]);
    }
}
