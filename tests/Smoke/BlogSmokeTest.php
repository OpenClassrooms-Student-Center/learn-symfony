<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Le Smoke testing permet d'identifier si on
 * a cassé quelque chose d'essentiel en production.
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
