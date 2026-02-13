<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    private function createUser(string $email, string $password): void
    {
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $hasher = $container->get('security.user_password_hasher');
        
        $existing = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $em->remove($existing);
            $em->flush();
        }
        
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setSlug('test-user-' . uniqid());
        $user->setPassword($hasher->hashPassword($user, $password));
        
        $em->persist($user);
        $em->flush();
    }

    public function testLogin(): void
    {
        $client = static::createClient();
        $this->createUser('zakariaeddouh.com', 'admin123');

        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'zakariaeddouh.com',
            'password' => 'admin123',
        ]);
        
        $client->submit($form);
        
        $this->assertResponseRedirects('/');
        $client->followRedirect();
        
        $this->assertSelectorExists('a[href="/logout"]');
    }

    public function testLoginInvalid(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'guez@guez.com',
            'password' => 'jesuisunhack3r',
        ]);
        
        $client->submit($form);
        
        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        
        $this->assertSelectorExists('.alert-danger');
    }
}
