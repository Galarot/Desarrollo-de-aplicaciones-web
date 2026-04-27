<?php

namespace App\Form;

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsuarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, [
                'disabled' => true,
                'attr' => ['readonly' => true],
            ])
            // Removido 'roles' del formulario para evitar error de array to string
            // ->add('roles')
            // Removido 'password' del formulario de edición
            // ->add('password')
            ->add('nombre')
            ->add('direccion')
            ->add('telefono')
            // Removidos campos de fecha que no deben editarse manualmente
            // ->add('createdAt')
            // ->add('deleteAt')
            // ->add('updatedAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
