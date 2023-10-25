<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Me;

use App\Entity\User;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group user-me
 * @group user-me-patch
 */
class PatchControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanPatch(string $user, int $id, array $patch, array $expected): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_PATCH, "/user/me", [], [], [], json_encode($patch));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $this->entityManager->clear();

        $patchedUser = $this->entityManager->getReference(User::class, $id);
        $response = \json_encode($this->serializer->normalize($patchedUser, 'json', ['groups' => ['default']]));

        $this->assertMatchesPattern(\json_encode($expected), $response, $response);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotPatch(string $user, array $patch, int $errorCode, array $expected): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_PATCH, "/user/me", [], [], [], \json_encode($patch));

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }
}