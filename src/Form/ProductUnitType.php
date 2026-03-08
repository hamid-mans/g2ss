<?php

namespace App\Form;

use App\Entity\Deposit;
use App\Entity\Product;
use App\Entity\ProductUnit;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductUnitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $company = $options['company'];
        $user = $options['user'];

        $builder
            ->add('serialNumber', TextType::class, [
                'label' => false
            ])
            ->add('buyPrice', NumberType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('deposit', EntityType::class, [
                'class' => Deposit::class,
                'query_builder' => function (EntityRepository $er) use ($company, $user) {
                    $qb = $er->createQueryBuilder('d')
                        ->where('d.company = :company')
                        ->setParameter('company', $company);

                    if ($user) {
                        $qb->innerJoin('d.users', 'u')
                            ->andWhere('u.id = :userId')
                            ->setParameter('userId', $user->getId());
                    }

                    return $qb->orderBy('d.name', 'ASC');
                },
                'choice_label' => 'name',
                'label' => false,
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
            'company' => null,
            'user' => null,
        ]);

        $resolver->setRequired('company');
    }
}
