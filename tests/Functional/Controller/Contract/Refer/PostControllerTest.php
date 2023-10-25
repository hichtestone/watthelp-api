<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Contract\Refer;

use App\Query\Criteria;
use App\Manager\ClientManager;
use App\Manager\ContractManager;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group contract
 * @group contract-refer
 * @group contract-refer-post
 */
class PostControllerTest extends FunctionalWebTestCase
{
    /**
     * @throws NonUniqueResultException
     */
    public function testCanPost(): void
    {
        $email = 'admin@test.fr';
        $this->connectUser($email);

        $clientManager = self::$container->get(ClientManager::class);
        $client = $clientManager->getByCriteria([new Criteria\Client\Id(1)]);
        
        $contractManager = self::$container->get(ContractManager::class);
        $contractToDuplicate = $contractManager->getByCriteria($client, [new Criteria\Contract\Id(1)]);

        $this->client->request(Request::METHOD_POST, "/contract/{$contractToDuplicate->getId()}/refer", [], [], [], \json_encode([]));

        $contractsUpdated = $contractManager->findByFilters($contractToDuplicate->getClient(), [
            'exclude_ids' => [
                $contractToDuplicate->getId()
            ]
        ]);

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());

        foreach ($contractsUpdated as $contractUpdated) {
            $this->assertEquals($contractUpdated->getInvoicePeriod(), $contractToDuplicate->getInvoicePeriod());
            $this->assertEquals($contractUpdated->getStartedAt(), $contractToDuplicate->getStartedAt());
            $this->assertEquals($contractUpdated->getFinishedAt(), $contractToDuplicate->getFinishedAt());
            $this->assertEquals($contractUpdated->getPricings(), $contractToDuplicate->getPricings());
        }
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotPost(string $user, int $id, int $expectedStatusCode, array $expectedErrors): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_POST, "/contract/$id/refer", [], [], [], \json_encode([]));

        $this->assertEquals($expectedStatusCode, $this->client->getResponse()->getStatusCode());
        $this->assertMatchesPattern(\json_encode($expectedErrors), $this->client->getResponse()->getContent());
    }
}