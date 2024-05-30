<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormConfigBuilderInterface;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('char_code', TextType::class, [
                'label' => 'Символьный код',
                'constraints' => [
                    new NotBlank(message: 'Эта строка не может быть пустой'),
                    new Length(['max'=>255], maxMessage:'Символьный код не должен превышать 255 символа')
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Название',
                'constraints' => [
                    new NotBlank(message: 'Эта строка не может быть пустой'),
                    new Length(['max'=>50], maxMessage:'Название не должно превышать 50 символов')
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание',
                'constraints' => [
                    new NotBlank(message: 'Эта строка не может быть пустой'),
                    new Length(['max'=>255], maxMessage:'Описание не должно превышать 255 символов')
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
