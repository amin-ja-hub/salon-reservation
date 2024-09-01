<?php

namespace App\Form;

use App\Entity\File;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName')
            ->add('username')
            ->add('plainPassword')
            ->add('password')
            ->add('cdate', null, [
                'widget' => 'single_text',
            ])
            ->add('udate', null, [
                'widget' => 'single_text',
            ])
            ->add('published')
            ->add('role')
            ->add('remove')
            ->add('mobile')
            ->add('email')
            ->add('image', EntityType::class, [
                'class' => File::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
