<?php

namespace App\Form;

use App\Entity\File;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Service; // Assuming this is the Service entity
use App\Repository\ServiceRepository;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('fullName')
            ->remove('username')
            ->remove('cdate', null, [
                'widget' => 'single_text',
            ])
            ->remove('udate', null, [
                'widget' => 'single_text',
            ])
            ->remove('published')
            ->remove('role')
            ->remove('remove')
            ->remove('image', EntityType::class, [
                'class' => File::class,
                'choice_label' => 'id',
            ])
    ->add('services', EntityType::class, [
        'class' => Service::class,  // The entity related to the field
        'choice_label' => 'title',  // Display the name of the service
        'multiple' => true,         // Allow multiple selections
        'expanded' => false,        // Dropdown with multi-select
        'query_builder' => function (ServiceRepository $er) {  // Correct type hint
            return $er->createQueryBuilder('c')
                      ->where('c.parent IS NOT NULL');  // Filtering services without a parent
        },
        'attr' => [
            'class' => 'form-select mb-2',   // Using Bootstrap 'form-select' for dropdown
            'data-control' => 'select2',     // Custom attribute for initializing Select2
            'data-placeholder' => 'انتخاب سرویس‌ها',  // Placeholder for Select2
        ],
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
