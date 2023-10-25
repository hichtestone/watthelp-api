<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Tax;

use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group tax
 * @group tax-post
 */
class PostControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCanPost(array $post, array $expected): void
    {
        $this->connectUser('admin@test.fr');
        $this->client->request(Request::METHOD_POST, '/tax', [], [], [], \json_encode($post));

        $response = $this->client->getResponse()->getContent();
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode(), $response);

        $this->assertMatchesPattern(\json_encode($expected), $response, $response);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotPost(string $user, array $post, int $expectedStatusCode, array $expectedErrors): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_POST, '/tax', [], [], [], \json_encode($post));

        $this->assertEquals($expectedStatusCode, $this->client->getResponse()->getStatusCode());
        $this->assertMatchesPattern(\json_encode($expectedErrors), $this->client->getResponse()->getContent());
    }
}