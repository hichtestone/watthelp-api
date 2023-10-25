<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Invoice\Analysis;

use App\Manager\Invoice\AnalysisManager;
use App\Tests\FunctionalWebTestCase;
use App\Tests\setExpandData;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group invoice-analysis
 * @group invoice-analysis-post
 */
class PostControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanPost(int $invoiceId, int $existingAnalyses, int $expectedAnalyses, array $expected): void
    {
        $this->connectUser('admin@test.fr');

        $client = $this->getUser('admin@test.fr')->getClient();
        $analysisFilters = [
            'invoices' => [$invoiceId]
        ];
        $analysisManager = self::$container->get(AnalysisManager::class);
        $this->assertEquals($existingAnalyses, count($analysisManager->findByFilters($client, $analysisFilters)));

        $this->client->request(Request::METHOD_POST, "/invoice/$invoiceId/analysis");

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());

        $this->assertEquals($expectedAnalyses, count($analysisManager->findByFilters($client, $analysisFilters)));
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException 
     */
    public function testCannotPost(string $user, int $invoiceId, int $errorCode, array $expectedError): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_POST, "/invoice/$invoiceId/analysis");

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}