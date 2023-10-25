<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Contract;

use App\Manager\ContractManager;
use App\Query\Criteria;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group contract
 * @group contract-delete
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

        $contractManager = self::$container->get(ContractManager::class);
        $contract = $contractManager->getByCriteria($user->getClient(), [new Criteria\Contract\Id($id)]);
        $this->assertNotNull($contract);

        $this->client->request(Request::METHOD_DELETE, "/contract/$id");

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
        $contract = $contractManager->getByCriteria($user->getClient(), [new Criteria\Contract\Id($id)]);
        $this->assertNull($contract);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotDelete(string $user, int $id, int $errorCode, array $expectedError): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_DELETE, "/contract/$id");

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}