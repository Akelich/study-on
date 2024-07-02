<?php

namespace App\Tests;

use App\Command\ResetSequencesCommand;
use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Service\BillingClient;
use App\Tests\Mock\BillingClientMock;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use App\Tests\Mock\MockBillingClient;

class AuthControllerTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }

    public function testOkLogin(): void
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
            'email' => 'user@mail.ru',
            'password' => 'password'
        ]);

        $client->submit($form);

        $this->assertSelectorTextContains('html', 'Курсы');
    }

    public function testFalseLogin(): void
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
            'email' => 'user@mail.ru',
            'password' => 'password1'
        ]);

        $client->submit($form);

        $this->assertSelectorTextContains('html', 'Пароль');

        
    }

    public function testOkRegistration(): void
    {
        $client = static::createTestClient();
        $client->disableReboot();

        $client->getContainer()->set(
            BillingClient::class,
            new MockBillingClient()
        );

        $crawler = $client->request('GET', '/registration');
        $this->assertResponseOk();

        $form = $crawler->selectButton('Зарегистрироваться')->form([
            'user_registration[email]' => 'newuser@email.ru',
            'user_registration[password][first]' => 'password',
            'user_registration[password][second]' => 'password',
        ]);

        $client->submit($form);

        $this->assertSelectorTextContains('html', 'Курсы');

    }

    public function testFalseRegistration(): void
    {
        $client = static::createTestClient();
        $client->disableReboot();

        $client->getContainer()->set(
            BillingClient::class,
            new MockBillingClient()
        );

        $crawler = $client->request('GET', '/registration');
        $this->assertResponseOk();

        $form = $crawler->selectButton('Зарегистрироваться')->form([
            'user_registration[email]' => 'newuser@mail.ru',
            'user_registration[password][first]' => 'password',
            'user_registration[password][second]' => 'passwordd',
        ]);

        $client->submit($form);

        $this->assertSelectorExists('.invalid-feedback');

        $form = $crawler->selectButton('Зарегистрироваться')->form([
            'user_registration[email]' => 'newuser@mail.ru',
            'user_registration[password][first]' => 'passw',
            'user_registration[password][second]' => 'passw',
        ]);

        $client->submit($form);

        $this->assertSelectorExists('.invalid-feedback');

        $form = $crawler->selectButton('Зарегистрироваться')->form([
            'user_registration[email]' => 'newuser@mail',
            'user_registration[password][first]' => 'password',
            'user_registration[password][second]' => 'password',
        ]);

        $client->submit($form);

        $this->assertSelectorExists('.invalid-feedback');
    }



}