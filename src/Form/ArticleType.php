<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Barchasb;
use App\Entity\Category;
use App\Entity\File;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('text')
            ->add('cdate', null, [
                'widget' => 'single_text',
            ])
            ->add('udate', null, [
                'widget' => 'single_text',
            ])
            ->add('metadesc')
            ->add('published')
            ->add('type')
            ->add('url')
            ->add('bazdid')
            ->add('remove')
            ->add('barchasbs', EntityType::class, [
                'class' => Barchasb::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('image', EntityType::class, [
                'class' => File::class,
                'choice_label' => 'id',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
