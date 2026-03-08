<?php

namespace App\Form;

use App\Entity\Deposit;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenerateUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $company = $options['company'];

        $builder
            ->add('firstname', TextType::class, [
                'label' => false,
            ])
            ->add('lastname', TextType::class, [
                'label' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => false,
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
            ])
            ->add('deposits', EntityType::class, [
                'label' => false,
                'class' => Deposit::class,
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (EntityRepository $er) use ($company) {
                    return $er->createQueryBuilder('d')
                        ->where('d.company = :company')
                        ->setParameter('company', $company)
                        ->orderBy('d.name', 'ASC');
                },
                'choice_label' => 'name',
                'by_reference' => false
            ])
            ->add('roles', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
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
            'data_class' => User::class,
            'submit_label' => 'Enregistrer',
            'submit_class' => 'btn btn-primary',
            'company' => null,
        ]);
    }
}