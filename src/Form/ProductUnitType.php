<?php

namespace App\Form;

use App\Entity\Deposit;
use App\Entity\Product;
use App\Entity\ProductUnit;
use App\Repository\DepositRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductUnitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $builder
            ->add('serialNumber', TextType::class, [
                'label' => false
            ])
            ->add('buyPrice', TextType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('deposit', EntityType::class, [
                'label' => false,
                'class' => Deposit::class,
                'choice_label' => 'name',
                'query_builder' => function (DepositRepository $repository) use ($user) {
                    return $repository->createQueryBuilder('d')
                        ->andWhere('d.company = :company')
                        ->setParameter('company', $user->getCompany())
                        ->orderBy('d.name', 'ASC');
                },
                'attr' => [
                    'class' => 'ui search dropdown'
                ]
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
            'data_class' => ProductUnit::class,
            'submit_label' => null,
            'submit_class' => null,
            'user' => null,
        ]);
    }
}
