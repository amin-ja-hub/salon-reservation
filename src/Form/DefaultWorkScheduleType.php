<?php

namespace App\Form;

use App\Entity\DefaultWorkSchedule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DefaultWorkScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dayOfWeek', ChoiceType::class, [
                'choices'  => [
                    'شنبه'     => 'شنبه',
                    'یکشنبه'   => 'یکشنبه',
                    'دوشنبه'   => 'دوشنبه',
                    'سه‌شنبه'  => 'سه‌شنبه',
                    'چهارشنبه' => 'چهارشنبه',
                    'پنج‌شنبه' => 'پنجشنبه',
                    'جمعه'     => 'جمعه',
                ],
                'placeholder' => 'انتخاب روز هفته',
            ])
            ->add('startTime', null, [
                'widget' => 'single_text',
            ])
            ->add('endTime', null, [
                'widget' => 'single_text',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DefaultWorkSchedule::class,
        ]);
    }
}
