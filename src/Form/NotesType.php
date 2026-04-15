<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Modules;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('notes', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'border border-base-300 flex-grow textarea textarea-ghost w-full h-full resize-none focus:outline-none p-0',
                    'placeholder' => "Notes de l'équipe...",
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => '<i class="ri ri-save-line"></i> Enregistrer',
                'attr' => [
                    'class' => 'btn btn-secondary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}
