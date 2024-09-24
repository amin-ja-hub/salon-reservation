<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cdate', null, [
                'widget' => 'single_text',
            ])
            ->add('udate', null, [
                'widget' => 'single_text',
            ])
            ->add('published')
            ->add('type')
            ->add('remove')
            ->add('reservationDateTime')
            ->add('personal', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'id',
            ])
            ->add('articleChild', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
