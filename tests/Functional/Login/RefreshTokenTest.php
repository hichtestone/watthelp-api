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
class RefreshTokenTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanRefreshToken(string $email, string $password, array $expected)
    {
        // Simple login
        $this->client->request(Request::METHOD_POST, '/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(compact('email', 'password')));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $loginData = json_decode($this->client->getResponse()->getContent(), true);

        // Refresh token
        $post = ['refresh_token' => $loginData['refresh_token']];
        $this->client->request(Request::METHOD_POST, '/token/refresh', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($post));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(json_encode($expected), $this->client->getResponse()->getContent());
    }

    public function testCanNotRefreshToken()
    {
        $post = ['refresh_token' => 'fakerefreshtoken'];
        $this->client->request(Request::METHOD_POST, '/token/refresh', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($post));
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals([
            'code' => 401,
            'message' => 'An authentication exception occurred.',
        ], $response);
    }
}