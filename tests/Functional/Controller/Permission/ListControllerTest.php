<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Permission;

use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group permission
 * @group permission-list
 */
class ListControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanList(?int $page, ?int $perPage, ?string $sort, ?string $sortOrder, ?array $filters, array $expected): void
    {
        $this->connectUser('admin@test.fr');

        $parameters = [];
        if ($page !== null) {
            $parameters['page'] = $page;
        }
        if ($perPage !== null) {
            $parameters['per_page'] = $perPage;
        }
        if ($sort !== null) {
            $parameters['sort'] = $sort;
        }
        if ($sortOrder !== null) {
            $parameters['sort_order'] = $sortOrder;
        }
        if ($filters !== null) {
            $parameters['filters'] = $filters;
        }

        $this->client->request(Request::METHOD_GET, '/permission', $parameters);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotList(?string $sort, ?array $filters, array $expectedError): void
    {
        $this->connectUser('admin@test.fr');

        $parameters = [];
        if ($sort !== null) {
            $parameters['sort'] = $sort;
        }
        if ($filters !== null) {
            $parameters['filters'] = $filters;
        }

        $this->client->request(Request::METHOD_GET, "/permission", $parameters);

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}