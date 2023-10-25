<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Invoice\Anomaly;

use App\Manager\Invoice\AnomalyManager;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group invoice
 * @group invoice-anomaly
 * @group invoice-anomaly-delete
 */
class DeleteControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanDelete(array $body, int $existingAnomaliesCount): void
    {
        $user = $this->getUser('admin@test.fr');
        $this->addAuthorization($user);
        $filters = $body['filters'] ?? [];

        $anomalyManager = self::$container->get(AnomalyManager::class);
        $anomaliesFound = $anomalyManager->findByFilters($user->getClient(), $filters);
        $this->assertEquals($existingAnomaliesCount, count($anomaliesFound));

        $this->client->request(Request::METHOD_POST, '/invoice/anomaly/delete', [], [], [], \json_encode($body));

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $anomaliesFound = $anomalyManager->findByFilters($user->getClient(), $filters);
        $this->assertEquals(0, count($anomaliesFound), 'The anomalies were not deleted successfully');
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotDelete(string $user, array $body, int $errorCode, array $expected): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_POST, '/invoice/anomaly/delete', [], [], [], \json_encode($body));

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }
}
