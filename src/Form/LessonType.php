<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Lesson;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormConfigBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;

class LessonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название',
                'constraints' => [
                    new NotBlank(message: 'Эта строка не может быть пустой'),
                    new Length(['max'=>50], maxMessage:'Название не должно превышать 50 символов')
                ]
            ])
            ->add('lesson_content', TextareaType::class, [
                'label' => 'Содержание урока',
                'constraints' => [
                    new NotBlank(message: 'Эта строка не может быть пустой'),
                    new Length(['max'=>1000], maxMessage:'Содержание урока 1000 символов')
                ]
            ])
            ->add('serial_number', TextType::class, [
                'label' => 'Номер урока',
                'constraints' => [
                    new NotBlank(message: 'Эта строка не может быть пустой'),
                    new Range(
                        notInRangeMessage: 'Значение поля должно быть в пределах от 1 до 500',
                        min: 1,
                        max: 500
                    )
                ]
            ])
            ->add('course', HiddenType::class, [
                'data' => null,
                'disabled' => 'true',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
        ]);
    }
}
