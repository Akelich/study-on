<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Tests\AbstractTest;
use App\DataFixtures\CourseFixtures;

class LessonControllerTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }

    public function testGetPostActionsResponseOk(): void
    {
        $client = $this->getClient();
        $lessons = $this->getEntityManager()->getRepository(Lesson::class)->findAll();
        foreach ($lessons as $lesson) 
        {
            // страница урока
            $client->request('GET', '/lessons/' . $lesson->getId());
            $this->assertResponseOk();

            // страница редактирования урока
            $client->request('GET', '/lessons/' . $lesson->getId() . '/edit');
            $this->assertResponseOk();
        }

        $client = $this->getClient();
        $lessons = $this->getEntityManager()->getRepository(Lesson::class)->findAll();
        foreach ($lessons as $lesson)
        {
            $client->request('GET', "/lessons/{$lesson->getId()}/edit");
            $this->assertResponseOk();

            $client->request('GET', "/lessons/{$lesson->getId()}");
            $this->assertResponseOk();

            $client->request('POST', "/lessons/{$lesson->getId()}/edit");
            $this->assertResponseOk();

            $client->request('POST', "/lessons/{$lesson->getId()}");
            $this->assertResponseRedirect();
        }
    }

    public function testSuccessLessonCreating(): void
    {
        // список курсов
        $client = $this->createTestClient();
        $crawler = $client->request('GET', '/courses/');  
        $this->assertResponseOk();

        // переход на первый курс
        $link = $crawler->filter('.link-course')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // переход на окно создания
        $link = $crawler->selectLink('Добавить урок')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'lesson[name]' => 'testName',
                'lesson[lesson_content]' => '10',
                'lesson[serial_number]' => 'test',
            ]
        );

        $client->submit($form);

        $this->assertSelectorTextContains('html', 'testName');
    }

    public function testLessonEdit(): void
    {
        // список курсов
        $client = $this->createTestClient();
        $crawler = $client->request('GET', '/courses/');  
        $this->assertResponseOk();

        // переход на первый курс
        $link = $crawler->filter('.link-course')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // переход на первый урок
        $link = $crawler->filter('.link-body-emphasis')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->selectLink('Редактировать')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'lesson[name]' => 'testName1',
                'lesson[lesson_content]' => '10',
                'lesson[serial_number]' => 'test',
            ]
        );

        $client->submit($form);

        $this->assertSelectorTextContains('html', 'testName1');
    }
}
