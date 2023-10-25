<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Notification;

use App\Manager\NotificationManager;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group notification
 * @group notification-delete
 */
class DeleteControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanDelete($ids, int $existingCount): void
    {
        $this->connectUser('admin@test.fr');
        $user = $this->getUser('admin@test.fr');

        $notificationManager = self::$container->get(NotificationManager::class);
        $filters = ['user' => $user];
        if (is_array($ids)) {
            $filters['id'] = $ids;
        }
        $notifications = $notificationManager->findByFilters($filters);
        $this->assertEquals($existingCount, count($notifications));
        $this->client->request(Request::METHOD_POST, '/notification/delete', [], [], [], \json_encode(['ids' => $ids]));

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
        $notifications = $notificationManager->findByFilters($filters);
        $this->assertEquals(0, count($notifications));
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotDelete($ids, int $errorCode, array $expectedError): void
    {
        $this->connectUser('admin@test.fr');

        $this->client->request(Request::METHOD_POST, '/notification/delete', [], [], [], \json_encode(['ids' => $ids]));

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}
