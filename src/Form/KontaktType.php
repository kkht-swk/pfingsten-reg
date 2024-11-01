<?php

namespace App\Form;

use App\Entity\Kontakt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KontaktType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vorname', TextType::class, [ 
                'label' => 'label.vorname',
                'attr' => [ 'placeholder' => 'placeholder.vorname' ],
            ])
            ->add('nachname', TextType::class, [ 
                'label' => 'label.nachname',
                'attr' => [ 'placeholder' => 'placeholder.nachname' ],
            ])
            ->add('email', EmailType::class, [ 
                'label' => 'label.email',
                'attr' => [ 'placeholder' => 'placeholder.email' ],
            ])
            ->add('phone', TextType::class, [ 
                'label' => 'label.phone',
                'attr' => [ 'placeholder' => 'placeholder.phone' ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Kontakt::class,
        ]);
    }
}
