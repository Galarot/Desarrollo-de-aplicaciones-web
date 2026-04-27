<?php

namespace App\Form;

use App\Entity\Cliente;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre completo',
                'constraints' => [
                    new NotBlank(message: 'Por favor, introduce tu nombre'),
                    new Length(
                        min: 3,
                        minMessage: 'El nombre debe tener al menos {{ limit }} caracteres',
                        max: 255
                    ),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(message: 'Por favor, introduce tu email'),
                ],
            ])
            ->add('telefono', IntegerType::class, [
                'required' => false,
            ])
            ->add('direccion', TextType::class, [
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(message: 'Por favor, introduce tu email'),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Contraseña',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'Repetir contraseña',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'Las contraseñas deben coincidir',
                'constraints' => [
                    new NotBlank(message: 'Por favor, introduce una contraseña'),
                    new Length(
                        min: 8,
                        minMessage: 'Tu contraseña debe tener al menos {{ limit }} caracteres',
                        max: 4096,
                    ),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Acepto los términos y condiciones',
                'constraints' => [
                    new IsTrue(message: 'Debes aceptar los términos y condiciones.'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cliente::class,
        ]);
    }
}
