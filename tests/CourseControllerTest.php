<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Tests\AbstractTest;
use App\DataFixtures\CourseFixtures;
use App\Service\BillingClient;
use App\Tests\Mock\MockBillingClient;


class CourseControllerTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }

    public function urlProviderSuccessful(): \Generator
    {
        yield ['/courses/'];
    }

    /**
     * @dataProvider urlProviderSuccessful
     */
    public function testPageSuccessful($url): void
    {
        $client = static::createTestClient();
        $client->disableReboot();

        $client->getContainer()->set(
            BillingClient::class,
            new MockBillingClient()
        );
        $client->request('GET', $url);
        $this->assertResponseOk();
    }

    public function testPageNotFound()
    {
        $client = static::createTestClient();
        $client->disableReboot();

        $client->getContainer()->set(
            BillingClient::class,
            new MockBillingClient()
        );
        $client->request('GET', "/nopage/");
        $this->assertResponseNotFound();
    }

    public function testGetPostActionsResponseOk(): void
    {
        $client = static::createTestClient();
        $client->disableReboot();

        $client->getContainer()->set(
            BillingClient::class,
            new MockBillingClient()
        );

        $courses = $this->getEntityManager()->getRepository(Course::class)->findAll();
        foreach ($courses as $course) 
        {           
            $client->request('GET', "/courses/{$course->getId()}");
            $this->assertResponseOk();

            $client->request('GET', "/courses/{$course->getId()}/edit");
            $this->assertResponseRedirect();

            $client->request('POST', "/courses/{$course->getId()}");
            $this->assertResponseRedirect();

            $client->request('POST', "/courses/{$course->getId()}/edit");
            $this->assertResponseRedirect();
        }
    }

    public function testSuccessCourseCreating(): void
    {
        $client = static::createTestClient();
        $client->disableReboot();

        $client->getContainer()->set(
            BillingClient::class,
            new MockBillingClient()
        );

        $crawler = $client->request('GET', '/login');
        $this->assertResponseOk();

        $form = $crawler->selectButton('Войти')->form([
            'email' => 'admin@mail.ru',
            'password' => 'password'
        ]);

        $client->submit($form);

        //dd($client->getKernel()->getContainer()->get('security.token_storage'));

        $countAfter = count($this->getEntityManager()->getRepository(Course::class)->findAll());
        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        // переход на окно добавления курса
        $link = $crawler->selectLink('Новый курс')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // заполнение формы корректными значениями
        $courseCreatingForm = $crawler->selectButton('Сохранить')->form([
            'course[char_code]' => 'test',
            'course[name]' => 'name',
            'course[description]' => 'description',
        ]);
        $client->submit($courseCreatingForm);

        // редирект
        $this->assertSame($client->getResponse()->headers->get('location'), '/courses/');
        $client->followRedirect();
        $this->assertResponseOk();

        // поиск новго курса
        $course = $this->getEntityManager()->getRepository(Course::class)->findOneBy([
            'char_code' => 'test',
        ]);
        $crawler = $client->request('GET', '/courses/' . $course->getId());
        $this->assertResponseOk();

        $countBefore = count($this->getEntityManager()->getRepository(Course::class)->findAll());

        $this->assertNotNull($course);
        $this->assertEquals($countAfter + 1, $countBefore);
        $this->assertResponseOk();
        }

    public function testFailCourseCreating(): void
    {
        // список курсов
        $client = static::createTestClient();
        $client->disableReboot();

        $client->getContainer()->set(
            BillingClient::class,
            new MockBillingClient()
        );

        $crawler = $client->request('GET', '/login');
        $this->assertResponseOk();

        $form = $crawler->selectButton('Войти')->form([
            'email' => 'user@mail.ru',
            'password' => 'password'
        ]);

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        // переход на окно добавления курса
        $link = $crawler->selectLink('Новый курс')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // заполнение формы корректными значениями(кроме кода)
        $courseCreatingForm = $crawler->selectButton('Сохранить')->form([
            'course[char_code]' => '',
            'course[name]' => 'Тест-имя',
            'course[description]' => 'Тест-описание',
        ]);
        $client->submit($courseCreatingForm);
        $this->assertResponseCode(422);
        $this->assertSelectorExists('.invalid-feedback');


        // заполнение формы корректными значениями(кроме названия)
        $courseCreatingForm = $crawler->selectButton('Сохранить')->form([
            'course[char_code]' => 'test',
            'course[name]' => '',
            'course[description]' => 'test',
        ]);
        $client->submit($courseCreatingForm);
        $this->assertResponseCode(422);
        $this->assertSelectorExists('.invalid-feedback');

        // заполнение формы корректными значениями(кроме кода)
        $courseCreatingForm = $crawler->selectButton('Сохранить')->form([
            'course[char_code]' => str_repeat("test", 64),
            'course[name]' => 'test',
            'course[description]' => 'test',
        ]);
        $client->submit($courseCreatingForm);
        $this->assertResponseCode(422);
        $this->assertSelectorExists('.invalid-feedback');

        // заполнение формы корректными значениями(кроме названия)
        $courseCreatingForm = $crawler->selectButton('Сохранить')->form([
            'course[char_code]' => 'test',
            'course[name]' => str_repeat("test", 13),
            'course[description]' => 'test',
        ]);
        $client->submit($courseCreatingForm);
        $this->assertResponseCode(422);
        $this->assertSelectorExists('.invalid-feedback');

        // заполнение формы корректными значениями(кроме описания)
        $courseCreatingForm = $crawler->selectButton('Сохранить')->form([
            'course[char_code]' => 'test',
            'course[name]' => 'test',
            'course[description]' => str_repeat("test", 64),
        ]);
        $client->submit($courseCreatingForm);
        $this->assertResponseCode(422);
        $this->assertSelectorExists('.invalid-feedback');
    }

    public function testCourseEdit(): void
    {
        // список курсов
        $client = static::createTestClient();
        $client->disableReboot();

        $client->getContainer()->set(
            BillingClient::class,
            new MockBillingClient()
        );

        $crawler = $client->request('GET', '/login');
        $this->assertResponseOk();

        $form = $crawler->selectButton('Войти')->form([
            'email' => 'user@mail.ru',
            'password' => 'password'
        ]);

        $crawler = $client->request('GET', '/courses/');  
        $this->assertResponseOk();

        // переход на первый курс
        $link = $crawler->filter('.link-course')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        // переход на страницу редактирования
        $link = $crawler->filter('.edit_course_link')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        //dd($crawler->filter('title')->first());
        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[char_code]' => 'test',
                'course[name]' => 'testN',
                'course[description]' => 'test',
            ]
        );
        $client->submit($form);
        $editedCourse = $this->getEntityManager()->getRepository(Course::class)->findOneBy([
            'char_code' => 'test',
        ]);
        $this->assertNotNull($editedCourse);
        $crawler = $client->request('GET', "/courses/{$editedCourse->getId()}");
        $this->assertResponseOk();
        $this->assertSame('testN',$crawler->filter('.course_name')->text());
    }

    public function testDeleteCourse(){
        $client = static::createTestClient();
        $client->disableReboot();

        $client->getContainer()->set(
            BillingClient::class,
            new MockBillingClient()
        );

        $crawler = $client->request('GET', '/login');
        $this->assertResponseOk();

        $form = $crawler->selectButton('Войти')->form([
            'email' => 'user@mail.ru',
            'password' => 'password'
        ]);

        $crawler = $this->getClient()->request('GET', '/courses/');
        $countBefore = count($this->getEntityManager()->getRepository(Course::class)->findAll());
        $this->assertResponseOk();

        $courseLink = $crawler->filter('.link-course')->link();
        $client->click($courseLink);
        $this->assertResponseOk();

        $client->submitForm('Удалить курс');
        $countAfter = count($this->getEntityManager()->getRepository(Course::class)->findAll());
        $this->assertSame($countAfter, $countBefore-1);
        $crawler = $client->followRedirect();
        $this->assertCount($countAfter, $crawler->filter('.card-body'));
    }
}
