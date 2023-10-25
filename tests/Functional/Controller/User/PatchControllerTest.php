<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Entity\User;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group user
 * @group user-patch
 */
class PatchControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanPatch(string $user, int $id, array $patch, array $expands, array $expected): void
    {
        $this->connectUser($user);

        if (!empty($expands)) {
            $this->setExpandData(implode(',', $expands));
        }

        $this->client->request(Request::METHOD_PATCH, "/user/$id", [], [], [], json_encode($patch));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $this->entityManager->clear();

        $patchedUser = $this->entityManager->getReference(User::class, $id);
        $response = \json_encode($this->serializer->normalize($patchedUser, 'json', ['groups' => array_merge(['default'], $expands)]));

        $this->assertMatchesPattern(\json_encode($expected), $response, $response);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotPatch(string $user, int $id, array $patch, int $errorCode, array $expected): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_PATCH, "/user/$id", [], [], [], \json_encode($patch));

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }
}