<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Invoice;

use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group invoice
 * @group invoice-get
 */
class GetControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanGet(string $user, int $id, ?string $expand, array $expected): void
    {
        $this->connectUser($user);

        if ($expand) {
            $this->setExpandData($expand);
        }
        
        $this->client->request(Request::METHOD_GET, "/invoice/$id");

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException 
     */
    public function testCannotGet(int $id, int $errorCode, array $expectedError): void
    {
        $this->connectUser('admin@test.fr');

        $this->client->request(Request::METHOD_GET, "/invoice/$id");

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}