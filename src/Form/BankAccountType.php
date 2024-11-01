<?php

namespace App\Form;

use App\Entity\BankAccount;
use App\Entity\Kontakt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('iban', TextType::class, [
                'label' => 'label.iban',
                'attr' => [ 'placeholder' => 'placeholder.iban' ],
            ])
            ->add('bic', TextType::class, [
                'label' => 'label.bic',
                'attr' => [ 'placeholder' => 'placeholder.bic' ],
            ])
            ->add('bank', TextType::class, [
                'label' => 'label.bank',
                'attr' => [ 'placeholder' => 'placeholder.bank' ],
            ])
            ->add('kontoinhaber', TextType::class, [
                'label' => 'label.kontoinhaber',
                'attr' => [ 'placeholder' => 'placeholder.kontoinhaber' ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BankAccount::class,
        ]);
    }

}