<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Product;
use App\Entity\TVA;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
                'attr' => [
                    'placeholder' => 'Designation',
                ]
            ])
            ->add('refInterne', TextType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank([], 'La référence interne est obligatoire.'),
                ],
                'error_bubbling' => true,
                'attr' => [
                    'placeholder' => 'Ex: SN-12345'
                ]
            ])
            ->add('refSupplier', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: RF-12345'
                ]
            ])
            ->add('sellPrice', NumberType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => '0,00 €'
                ]
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
            ->add('category', EntityType::class, [
                'label' => false,
                'class' => Category::class,
                'required' => false,
                'placeholder' => "Aucune catégorie",
                'choice_label' => 'label',
            ])
            ->add('color', EntityType::class, [
                'label' => false,
                'class' => Color::class,
                'required' => false,
                'placeholder' => "Aucune couleur",
                'choice_label' => 'label',
            ])
            ->add('weightKg', NumberType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('length', NumberType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('width', NumberType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('height', NumberType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('tva', EntityType::class, [
                'label' => false,
                'class' => Tva::class,
                'required' => false,
                'placeholder' => "Aucune TVA",
                'choice_label' => function (Tva $tva) {
                    return $tva->getValue() . ' %';
                },
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
            'data_class' => Product::class,
            'submit_label' => false,
            'submit_class' => null
        ]);
    }
}
