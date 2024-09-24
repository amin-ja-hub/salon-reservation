<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('parent', EntityType::class, [
            'class' => Service::class,
            'choice_label' => 'title',
            'placeholder' => 'انتخاب زیر مجموعه', // This adds a null option
            'required' => false,  // Allow null selection
            'attr' => [
                'class' => 'form-select mb-2',
                'data-control' => 'select2',
                'data-placeholder' => 'انتخاب زیر مجموعه',
            ],
        ])
        ->remove('category', EntityType::class, [
            'class' => Category::class,
            'choice_label' => 'title',
            'placeholder' => 'انتخاب دسته بندی', // Corrected placeholder
            'required' => false,  // Allow null selection
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')
                    ->where('c.type = :type')
                    ->setParameter('type', 2);
            },            
            'attr' => [
                'class' => 'form-select mb-2',
                'data-control' => 'select2',
                'data-placeholder' => 'انتخاب دسته بندی',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
