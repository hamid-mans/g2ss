<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Modules;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false
            ])
            ->add('address1', TextType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('address2', TextType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('cop', TextType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('city', TextType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('modules', EntityType::class, [
                'label' => false,
                'multiple' => true,
                'class' => Modules::class,
                'choice_label' => 'label',
                'attr' => [
                    'class' => 'ui search dropdown',
                ]
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
            'data_class' => Company::class,
            'submit_label' => false,
            'submit_class' => false,
        ]);
    }
}
