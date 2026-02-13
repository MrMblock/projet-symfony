<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Derniers articles');
    }

    public function testPostShow(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        
        $crawler = $client->request('GET', '/post/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Articles');
    }
}
