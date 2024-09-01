<?php

namespace App\Form;

use App\Entity\Barchasb;
use App\Entity\Category;
use App\Entity\File;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('url')
            ->add('cdate', null, [
                'widget' => 'single_text',
            ])
            ->add('udate', null, [
                'widget' => 'single_text',
            ])
            ->add('published')
            ->add('type')
            ->add('metaKey')
            ->add('metadesc')
            ->add('remove')
            ->add('barchasbs', EntityType::class, [
                'class' => Barchasb::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('parent', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'id',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('image', EntityType::class, [
                'class' => File::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
