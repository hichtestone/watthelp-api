<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Tax;

use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group tax
 * @group tax-list
 */
class ListControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanList(string $user, ?int $page, ?int $perPage, ?string $sort, ?string $sortOrder, array $expected): void
    {
        $this->connectUser($user);

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

        $this->client->request(Request::METHOD_GET, '/tax', $parameters);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotList(?string $sort, array $expectedError): void
    {
        $this->connectUser('admin@test.fr');

        $this->client->request(Request::METHOD_GET, "/tax", ['sort' => $sort]);

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}