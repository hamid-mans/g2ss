<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductProviderComparative;
use App\Entity\Provider;
use App\Repository\ProviderRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComparativeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $company = $options['company'];

        $builder
            ->add('buyPrice', TextType::class, [
                'label' => false,
                'required' => true,
            ])
            ->add('provider', EntityType::class, [
                'class' => Provider::class,
                'placeholder' => "Aucun fournisseur",
                'choice_label' => 'name',
                'required' => true,
                'query_builder' => function (ProviderRepository $er) use ($company) {
                    return $er->createQueryBuilder('d')
                        ->where('d.company = :company')
                        ->setParameter('company', $company);
                }
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
            'data_class' => ProductProviderComparative::class,
            'submit_label' => null,
            'submit_class' => null,
            'company' => null,
        ]);
    }
}
