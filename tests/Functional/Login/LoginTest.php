<?php

declare(strict_types=1);

namespace App\Tests\Functional\Login;

use App\Entity\User;
use App\Tests\FunctionalWebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group login
 */
class LoginTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanLogin(string $email, string $password, string $expand, array $expected)
    {
        $this->setExpandData($expand);

        $this->client->request(Request::METHOD_POST, '/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(compact('email', 'password')));

        $response = $this->client->getResponse()->getContent();

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $response);
        $this->assertMatchesPattern(json_encode($expected), $response, $response);
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testCannotLogin(string $email, string $password, array $expected): void
    {
        $post = compact('email', 'password');
        $this->client->request(Request::METHOD_POST, '/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($post));

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $this->assertMatchesPattern(json_encode($expected), $this->client->getResponse()->getContent());
    }
}