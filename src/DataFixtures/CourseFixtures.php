<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Course;
use App\Entity\Lesson;

class CourseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $course1 = new Course();
        $course1 -> setCharCode('A1')
                -> setName('Курс по вышиванию')
                -> setDescription('На этом курсе Вы научитесь вышивать');

        $manager->persist($course1);

        $lesson11 = new Lesson();
        $lesson11 -> setName('Что такое вышивание?')
                -> setLessonContent('Рассказываем о вышивании')
                -> setSerialNumber('1');
        
        $lesson11->setCourse($course1);
        
        $manager->persist($lesson11);

        $lesson12 = new Lesson();
        $lesson12 -> setName('Первые шаги')
                -> setLessonContent('Рассматриваем основы')
                -> setSerialNumber('2');
        
        $lesson12->setCourse($course1);
        
        $manager->persist($lesson12);

        $lesson13 = new Lesson();
        $lesson13 -> setName('Последние шаги')
                -> setLessonContent('Закрепляем материал')
                -> setSerialNumber('3');
        
        $lesson13->setCourse($course1);
        
        $manager->persist($lesson13);


        $course2 = new Course();
        $course2 -> setCharCode('A2')
                -> setName('Поварской курс')
                -> setDescription('На этом курсе Вы научитесь готовить');

        $manager->persist($course2);

        $lesson21 = new Lesson();
        $lesson21 -> setName('Готовим борщ')
                -> setLessonContent('Все о приготовлении первых блюд')
                -> setSerialNumber('1');
        
        $lesson21->setCourse($course2);
        
        $manager->persist($lesson21);

        $lesson22 = new Lesson();
        $lesson22 -> setName('Готовим пюре с котлетой')
                -> setLessonContent('Все о приготовлении вторых блюд')
                -> setSerialNumber('2');
        
        $lesson22->setCourse($course2);
        
        $manager->persist($lesson22);

        $lesson23 = new Lesson();
        $lesson23 -> setName('Варим компот')
                -> setLessonContent('Все о приготовлении третьего')
                -> setSerialNumber('3');
        
        $lesson23->setCourse($course2);
        
        $manager->persist($lesson23);

        $manager->flush();
    }
}
