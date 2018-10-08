<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Le Smoke testing permet de rapidement identifier si on
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

        self::assertTrue($client->getResponse()->isSuccessful());
        self::assertSame(
            $crawler->filter('title')->text(),
            'Le blog de Zozor!'
        );
    }
}
