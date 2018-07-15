<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test for the controllers defined inside the BlogController used
 * for managing the blog in the backend.
 *
 * See https://symfony.com/doc/current/book/testing.html#functional-tests
 *
 * Whenever you test resources protected by a firewall, consider using the
 * technique explained in:
 * https://symfony.com/doc/current/cookbook/testing/http_authentication.html
 *
 * Execute the application tests using this command (requires PHPUnit to be installed):
 *
 *     $ cd your-symfony-project/
 *     $ ./vendor/bin/phpunit
 */
class BlogControllerTest extends WebTestCase
{
    /**
     * @dataProvider getUrlsForRegularUsers
     */
    public function testAccessDeniedForRegularUsers(string $httpMethod, string $url)
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'john_user',
            'PHP_AUTH_PW' => 'kitten',
        ]);

        $client->request($httpMethod, $url);
        self::assertSame(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());
    }

    public function getUrlsForRegularUsers()
    {
        yield ['GET', '/admin/post/'];
        yield ['GET', '/admin/post/1'];
        yield ['GET', '/admin/post/1/edit'];
        yield ['POST', '/admin/post/1/delete'];
    }

    public function testAdminBackendHomePage()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);

        $crawler = $client->request('GET', '/admin/post/');
        self::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        self::assertGreaterThanOrEqual(
            1,
            $crawler->filter('body#admin_post_index #main tbody tr')->count(),
            'The backend homepage displays all the available posts.'
        );
    }

    /**
     * This test changes the database contents by creating a new blog post. However,
     * thanks to the DAMADoctrineTestBundle and its PHPUnit listener, all changes
     * to the database are rolled back when this test completes. This means that
     * all the application tests begin with the same database contents.
     */
    public function testAdminNewPost()
    {
        $postTitle = 'Blog Post Title '.mt_rand();
        $postSummary = $this->generateRandomString(255);
        $postContent = $this->generateRandomString(1024);

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $crawler = $client->request('GET', '/admin/post/new');

        $form = $crawler->selectButton('Create post')->form([
            'post[title]' => $postTitle,
            'post[summary]' => $postSummary,
            'post[content]' => $postContent,
        ]);
        $client->submit($form);

        self::assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $post = $client->getContainer()->get('doctrine')->getRepository(Post::class)->findOneBy([
            'title' => $postTitle,
        ]);
        self::assertNotNull($post);
        self::assertSame($postSummary, $post->getSummary());
        self::assertSame($postContent, $post->getContent());
    }

    public function testAdminShowPost()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $client->request('GET', '/admin/post/1');

        self::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    /**
     * This test changes the database contents by editing a blog post. However,
     * thanks to the DAMADoctrineTestBundle and its PHPUnit listener, all changes
     * to the database are rolled back when this test completes. This means that
     * all the application tests begin with the same database contents.
     */
    public function testAdminEditPost()
    {
        $newBlogPostTitle = 'Blog Post Title '.mt_rand();

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $crawler = $client->request('GET', '/admin/post/1/edit');
        $form = $crawler->selectButton('Save changes')->form([
            'post[title]' => $newBlogPostTitle,
        ]);
        $client->submit($form);

        self::assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        /** @var Post $post */
        $post = $client->getContainer()->get('doctrine')->getRepository(Post::class)->find(1);
        self::assertSame($newBlogPostTitle, $post->getTitle());
    }

    /**
     * This test changes the database contents by deleting a blog post. However,
     * thanks to the DAMADoctrineTestBundle and its PHPUnit listener, all changes
     * to the database are rolled back when this test completes. This means that
     * all the application tests begin with the same database contents.
     */
    public function testAdminDeletePost()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $crawler = $client->request('GET', '/admin/post/1');
        $client->submit($crawler->filter('#delete-form')->form());

        self::assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $post = $client->getContainer()->get('doctrine')->getRepository(Post::class)->find(1);
        self::assertNull($post);
    }

    private function generateRandomString(int $length): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return mb_substr(str_shuffle(str_repeat($chars, ceil($length / mb_strlen($chars)))), 1, $length);
    }
}
