<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Budget\Export;

use App\Manager\NotificationManager;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group export
 * @group budget
 * @group budget-export
 * @group budget-export-post
 */
class PostControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCanPost(array $post, string $expectedContent): void
    {
        $this->connectUser('admin@test.fr');

        $this->client->request(Request::METHOD_POST, '/budget/export', [], [], [], \json_encode($post));

        $response = $this->client->getResponse()->getContent();

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode(), $response);
        $this->assertMatchesPattern(\json_encode([]), $response, $response);

        $notificationManager = self::$container->get(NotificationManager::class);
        $notifications = iterator_to_array($notificationManager->findByFilters(['user' => 1, 'message' => 'Export terminé avec succès.']));
        $this->assertEquals(1, count($notifications));

        $sheetContent = $this->getSheet($notifications[0]->getUrl());
        $this->assertEquals(json_decode($expectedContent), $sheetContent);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotPost(string $user, array $post, int $expectedStatusCode, array $expectedResponse): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_POST, '/budget/export', [], [], [], \json_encode($post));

        $this->assertEquals($expectedStatusCode, $this->client->getResponse()->getStatusCode());
        $this->assertMatchesPattern(\json_encode($expectedResponse), $this->client->getResponse()->getContent());
    }
}