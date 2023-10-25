<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Invoice\DeliveryPointInvoice\Export;

use App\Manager\NotificationManager;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group export
 * @group delivery-point-invoice
 * @group delivery-point-invoice-export
 */
class PostControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCanPost(string $userEmail, array $parameters, string $expectedContent): void
    {
        $user = $this->getUser($userEmail);
        $this->addAuthorization($user);

        $this->client->request(Request::METHOD_POST, '/delivery-point-invoice/export', [], [], [], \json_encode($parameters));

        $response = $this->client->getResponse()->getContent();

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode(), $response);
        $this->assertMatchesPattern(\json_encode([]), $response, $response);

        $notificationManager = self::$container->get(NotificationManager::class);
        $notifications = iterator_to_array($notificationManager->findByFilters(['user' => $user->getId(), 'message' => 'Export terminé avec succès.']));
        $this->assertEquals(1, count($notifications));

        $sheetContent = $this->getSheet($notifications[0]->getUrl());
        $this->assertEquals(json_decode($expectedContent), $sheetContent);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotPost(string $user, array $parameters, int $expectedStatusCode, array $expectedResponse): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_POST, '/delivery-point-invoice/export', [], [], [], \json_encode($parameters));

        $this->assertEquals($expectedStatusCode, $this->client->getResponse()->getStatusCode());
        $this->assertMatchesPattern(\json_encode($expectedResponse), $this->client->getResponse()->getContent());
    }
}