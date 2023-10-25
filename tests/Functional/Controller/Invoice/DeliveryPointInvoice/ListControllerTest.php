<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Invoice\DeliveryPointInvoice;

use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group invoice
 * @group delivery-point-invoice
 * @group delivery-point-invoice-list
 */
class ListControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanList(string $user, array $parameters, array $expected): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_GET, '/delivery-point-invoice', $parameters);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotList(string $user, array $parameters, int $errorCode, array $expectedError): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_GET, "/delivery-point-invoice", $parameters);

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}