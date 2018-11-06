<?php

namespace App\Tests\Smoke;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Smoke testing is used to quickly identify bugs
 * in most important features of your application.
 *
 *     $ cd your-symfony-project/
 *     $ ./vendor/bin/simple-phpunit
 */
class BlogSmokeTest extends WebTestCase
{
    public function testHomePageIsAvailable()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        self::assertSame('Le blog de Zozor!', $crawler->filter('title')->text());
    }
}
