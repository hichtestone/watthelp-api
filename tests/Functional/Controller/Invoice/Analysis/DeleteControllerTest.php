<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Invoice\Analysis;

use App\Manager\Invoice\AnalysisManager;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group invoice
 * @group invoice-analysis
 * @group invoice-analysis-delete
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

        $analysisFilters = $body['filters'] ?? [];
        $analysisManager = self::$container->get(AnalysisManager::class);
        $this->assertEquals($existingAnomaliesCount, count($analysisManager->findByFilters($user->getClient(), $analysisFilters)));

        $this->client->request(Request::METHOD_POST, "/invoice/analysis/delete", [], [], [], \json_encode($body));

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $this->assertEquals(0, count($analysisManager->findByFilters($user->getClient(), $analysisFilters)));
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotDelete(string $user, array $body, int $errorCode, array $expected): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_POST, "/invoice/analysis/delete", [], [], [], \json_encode($body));

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }
}
