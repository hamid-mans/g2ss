<?php

namespace App\Form\Search;

use App\Entity\Deposit;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchDepositType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $company = $options['company'];
        $user = $options['user'];

        $builder
            ->add('deposits', EntityType::class, [
                'label' => false,
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
                'choice_label' => function (Deposit $deposit) {
                    return $deposit->getName();
                },
                'attr' => [
                    'class' => 'mb-3',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'company' => null,
            'user' => null,
        ]);
    }
}
