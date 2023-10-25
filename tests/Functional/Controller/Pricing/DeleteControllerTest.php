<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Pricing;

use App\Query\Criteria;
use App\Manager\PricingManager;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group pricing
 * @group pricing-delete
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

        $pricingManager = self::$container->get(PricingManager::class);
        $pricing = $pricingManager->getByCriteria($user->getClient(), [new Criteria\Pricing\Id($id)]);
        $this->assertNotNull($pricing);

        $this->client->request(Request::METHOD_DELETE, "/pricing/$id");

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
        $pricing = $pricingManager->getByCriteria($user->getClient(), [new Criteria\Pricing\Id($id)]);
        $this->assertNull($pricing);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotDelete(string $user, int $id, int $errorCode, array $expectedError): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_DELETE, "/pricing/$id");

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}