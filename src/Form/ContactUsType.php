<?php

namespace App\Form;

use App\Entity\ContactUs;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ContactUsType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('fullName', TextType::class, [
            'attr' => [
                'id' => 'name',
                'class' => 'form-control',
                'placeholder' => 'Name',
                'required' => true,
                'data-error' => 'Please enter your name',
            ],
        ])
        ->add('email', EmailType::class, [
            'attr' => [
                'id' => 'email',
                'class' => 'form-control',
                'placeholder' => 'Email',
                'required' => true,
                'data-error' => 'Please enter your email',
            ],
        ])
        ->add('mobile', TextType::class, [
            'attr' => [
                'id' => 'phone_number',
                'class' => 'form-control',
                'placeholder' => 'Phone',
                'required' => true,
                'data-error' => 'Please enter your number',
            ],
        ])
        ->add('subject', TextType::class, [
            'attr' => [
                'id' => 'msg_subject',
                'class' => 'form-control',
                'placeholder' => 'Subject',
                'required' => true,
                'data-error' => 'Please enter your subject',
            ],
        ])
        ->add('text', TextareaType::class, [
            'attr' => [
                'id' => 'message',
                'class' => 'form-control',
                'cols' => '30',
                'rows' => '8',
                'placeholder' => 'Write message',
                'required' => true,
                'data-error' => 'Write your message',
            ],
        ])
        ->remove('cdate')
        ->remove('published')
        ->remove('type')
        ->remove('remove');
} 
    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactUs::class,
        ]);
    }
}
