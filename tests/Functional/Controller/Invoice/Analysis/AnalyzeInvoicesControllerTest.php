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
 * @group invoice-analysis
 * @group invoice-analysis-by-filters
 */
class AnalyzeInvoicesControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanAnalyzeInvoices(array $post, array $existingAnalyses, array $expectedAnalyses): void
    {
        $this->connectUser('admin@test.fr');

        $client = $this->getUser('admin@test.fr')->getClient();

        $filtersExisting = [
            'ids' => $existingAnalyses
        ];
        $filtersExpected = [
            'ids' => $expectedAnalyses
        ];
        $analysisManager = self::$container->get(AnalysisManager::class);
        $expectedAnalysesFound = $analysisManager->findByFilters($client, $filtersExpected);
        $this->assertEquals(0, count($expectedAnalysesFound));

        if (!empty($existingAnalyses)) {
            $existingAnalysesFound = $analysisManager->findByFilters($client, $filtersExisting);
            $this->assertEquals(count($existingAnalyses), count($existingAnalysesFound));            
        }

        $this->client->request(Request::METHOD_POST, 'invoice/analysis', [], [], [], \json_encode($post));

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode([]), $this->client->getResponse()->getContent());

        if (!empty($existingAnalyses)) {
            $existingAnalysesFound = $analysisManager->findByFilters($client, $filtersExisting);
            $this->assertEquals(0, count($existingAnalysesFound));            
        }

        $expectedAnalysesFound = $analysisManager->findByFilters($client, $filtersExpected);
        $this->assertEquals(count($expectedAnalyses), count($expectedAnalysesFound));
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotAnalyzeInvoices(string $user, array $post, int $errorCode, array $expectedError): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_POST, 'invoice/analysis', [], [], [], \json_encode($post));

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}