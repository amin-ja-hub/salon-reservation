<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Barchasb;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BarchasbType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('cdate', null, [
                'widget' => 'single_text',
            ])
            ->add('published')
            ->add('type')
            ->add('articles', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('product', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Barchasb::class,
        ]);
    }
}
