<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Invoice\Import;

use App\Query\Criteria;
use App\Manager\ClientManager;
use App\Manager\ContractManager;
use App\Manager\DeliveryPointManager;
use App\Manager\InvoiceManager;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group import
 * @group invoice
 * @group invoice-import
 * @group invoice-import-post
 */
class PostControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCanPost(string $user, array $post, array $invoiceReferences, int $existingInvoices, array $deliveryPointReferences, array $contractReferences, array $expected): void
    {
        $this->connectUser($user);

        $invoiceManager = self::$container->get(InvoiceManager::class);
        $deliveryPointManager = self::$container->get(DeliveryPointManager::class);
        $contractManager = self::$container->get(ContractManager::class);

        $this->assertEquals($existingInvoices, $invoiceManager->count(['reference' => $invoiceReferences]));
        $this->assertEquals(0, $deliveryPointManager->count(['reference' => $deliveryPointReferences]));
        $this->assertEquals(0, $contractManager->count(['reference' => $contractReferences]));

        $this->client->request(Request::METHOD_POST, '/invoice/import', [], [], [], \json_encode($post));

        $response = $this->client->getResponse()->getContent();
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode(), $response);

        $this->assertEquals(count($invoiceReferences), $invoiceManager->count(['reference' => $invoiceReferences]));
        $this->assertEquals(count($deliveryPointReferences), $deliveryPointManager->count(['reference' => $deliveryPointReferences]));
        $this->assertEquals(count($contractReferences), $contractManager->count(['reference' => $contractReferences]));

        $this->assertMatchesPattern(\json_encode($expected), $response, $response);
    }
    
    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotPost(string $user, array $post, int $expectedStatusCode, array $expectedResponse): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_POST, '/invoice/import', [], [], [], \json_encode($post));

        $this->assertEquals($expectedStatusCode, $this->client->getResponse()->getStatusCode());
        $this->assertMatchesPattern(\json_encode($expectedResponse), $this->client->getResponse()->getContent());
    }
}