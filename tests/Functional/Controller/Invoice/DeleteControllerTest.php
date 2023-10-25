<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Invoice;

use App\Query\Criteria;
use App\Manager\InvoiceManager;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group invoice
 * @group invoice-delete
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

        $invoiceManager = self::$container->get(InvoiceManager::class);
        $invoice = $invoiceManager->getByCriteria($user->getClient(), [new Criteria\Invoice\Id($id)]);
        $this->assertNotNull($invoice);

        $this->client->request(Request::METHOD_DELETE, "/invoice/$id");

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
        $invoice = $invoiceManager->getByCriteria($user->getClient(), [new Criteria\Invoice\Id($id)]);
        $this->assertNull($invoice);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotDelete(string $user, int $id, int $errorCode, array $expectedError): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_DELETE, "/invoice/$id");

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}