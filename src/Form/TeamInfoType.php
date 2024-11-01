<?php

namespace App\Form;

use App\Entity\TeamInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

class TeamInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('verein', TextType::class, [ 
                'label' => 'label.verein',
                'attr' => [ 
                    'placeholder' => 'placeholder.verein' 
                ],
            ])
            ->add('altersklasse', ChoiceType::class, [
                'label' => 'label.altersklasse',
                'choices'  => [
                    'label.choose' => null,
                    'label.wU12' => 'wU12',
                    'label.mU12' => 'mU12',
                    'label.wU14' => 'wU14',
                    'label.mU14' => 'mU14',
                ]
            ])
            ->add('ankunftszeit', TextType::class, [
                'label' => 'label.ankunftszeit',
                'attr' => [ 'placeholder' => 'placeholder.ankunftszeit' ],
            ])
            ->add('teamname', TextType::class, [
                'label' => 'label.teamname',
                'attr' => [ 'placeholder' => 'placeholder.teamname' ],
            ])

            ->add('kontakt', KontaktType::class)

            ->add('spielervegan', IntegerType::class, [
                'attr' => [ 'placeholder' => 'placeholder.anzahl' ],
                'label' => 'label.spieler.vegan',
                'constraints' => [
                    new NotBlank([], 'error.anzahl.notblank' ),
                    new GreaterThanOrEqual(0, null, 'error.anzahl.greaterEqualZero'),
                ],
                // 'empty_data' => 0
            ])
            ->add('spielerfleisch', IntegerType::class, [
                'attr' => [ 'placeholder' => 'placeholder.anzahl' ],
                'label' => 'label.spieler.fleisch',
                'constraints' => [
                    new NotBlank([], 'error.anzahl.notblank' ),
                    new GreaterThanOrEqual(0, null, 'error.anzahl.greaterEqualZero'),
                ],
                // 'empty_data' => 0
            ])
            ->add('betreuervegan', IntegerType::class, [
                'attr' => [ 'placeholder' => 'placeholder.anzahl' ],
                'label' => 'label.betreuer.vegan',
                'constraints' => [
                    new NotBlank([], 'error.anzahl.notblank' ),
                    new GreaterThanOrEqual(0, null, 'error.anzahl.greaterEqualZero'),
                ],
                // 'empty_data' => 0
            ])
            ->add('betreuerfleisch', IntegerType::class, [
                'attr' => [ 'placeholder' => 'placeholder.anzahl' ],
                'label' => 'label.betreuer.fleisch',
                'constraints' => [
                    new NotBlank([], 'error.anzahl.notblank' ),
                    new GreaterThanOrEqual(0, null, 'error.anzahl.greaterEqualZero'),
                ],

                // 'empty_data' => 0
            ])

            ->add('gaeste', IntegerType::class, [
                'label' => 'label.gaeste',
                'attr' => [ 'placeholder' => 'placeholder.gaeste' ],
            ])
            ->add('bemerkung', TextareaType::class, [
                'label' => 'label.bemerkung',
                'attr' => [ 'placeholder' => 'placeholder.bemerkung' ],
            ])

            ->add('logo', FileType::class, [
                'label' => false,

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using attributes
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'error.logo.max_size',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpg',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'error.logo.format',
                    ])
                ],
            ])
            ->add('picture', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                // unmapped fields can't define their validation using attributes
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '4M',
                        'maxSizeMessage' => 'error.picture.max_size',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpg',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'error.picture.format',
                    ])
                ],
            ])

            ->add('account', BankAccountType::class)

            ->add('submit', SubmitType::class, [
                'label' => 'label.submit'
            ])
            
            ->setDisabled(true)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TeamInfo::class,
            'attr' => [ 
                'novalidate' => 'novalidate', // comment me to reactivate the html5 validation!  🚥
                // 'class' => 'needs-validation' 
            ], 
        ]);
    }

}