<?php

namespace App\Form;

use App\Entity\PlayerInfo;
use App\Form\KontaktType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vorname', TextType::class, [ 
                'attr' => [ 'placeholder' => 'Vorname' ],
            ])
            ->add('nachname', TextType::class, [
                'attr' => [ 'placeholder' => 'Nachname' ],
            ])
            ->add('altersklasse', ChoiceType::class, [
                'choices'  => [
                    'Wählen...' => null,
                    'wU12' => 'wU12',
                    'mU12' => 'mU12',
                    'wU14' => 'wU14',
                    'mU14' => 'mU14',
                ]
            ])
            ->add('nahrung',  ChoiceType::class, [
                'label' => 'Ernährung',
                'choices'  => [
                    'Wählen...' => null,
                    'vegan' => 'vegan',
                    'fleischhaltig' => 'fleischhaltig',
                ]
            ])
            ->add('schuhgroesse', ChoiceType::class, [
                'label' => 'Schuhgröße',
                'choices'  => [
                    'Wählen...' => null,
                    '32-34' => 'xs',
                    '35-37' => 's',
                    '38-40' => 'm',
                    '41-43' => 'l',
                    '44-46' => 'xl',
                    '47-49' => 'xxl',
                    '50-53' => '3xl',
                ]
            ])
            ->add('kontakt', KontaktType::class)
            ->add('account', BankAccountType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'Absenden'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlayerInfo::class,
            'attr' => [ 
                'novalidate' => 'novalidate', // comment me to reactivate the html5 validation!  🚥
                // 'class' => 'needs-validation' 
            ], 
        ]);
    }
}
