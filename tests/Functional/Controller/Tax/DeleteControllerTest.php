<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Tax;

use App\Query\Criteria;
use App\Manager\TaxManager;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group tax
 * @group tax-delete
 */
class DeleteControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanDelete(int $id): void
    {
        $this->connectUser('admin@test.fr');
        $user = $this->getUser('admin@test.fr');

        $taxManager = self::$container->get(TaxManager::class);
        $tax = $taxManager->getByCriteria($user->getClient(), [new Criteria\Tax\Id($id)]);
        $this->assertNotNull($tax);

        $this->client->request(Request::METHOD_DELETE, "/tax/$id");

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
        $tax = $taxManager->getByCriteria($user->getClient(), [new Criteria\Tax\Id($id)]);
        $this->assertNull($tax);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotDelete(string $user, int $id, int $errorCode, array $expectedError): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_DELETE, "/tax/$id");

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}