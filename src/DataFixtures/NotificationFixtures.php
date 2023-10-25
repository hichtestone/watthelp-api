<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Import;
use App\Entity\Notification;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class NotificationFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $user1 = $this->getReference('user-1');
        $user2 = $this->getReference('user-2');
        $importReport1 = $this->getReference('import-report-1');
        $importReport3 = $this->getReference('import-report-3');

        $notificationAnomalyUnread = new Notification();
        $notificationAnomalyUnread->setUser($user1);
        $notificationAnomalyUnread->setMessage('Le rapport de l\'import est disponible');
        $notificationAnomalyUnread->setUrl('');
        $notificationAnomalyUnread->setData(['report_id' => $importReport1->getId(), 'report_type' => Import::TYPE_INVOICE]);
        $notificationAnomalyUnread->setIsRead(false);
        $notificationAnomalyUnread->setCreatedAt(new \DateTime('2020-02-05'));
        $notificationAnomalyUnread->setUpdatedAt(null);
        $manager->persist($notificationAnomalyUnread);

        $notificationAnomalyRead = new Notification();
        $notificationAnomalyRead->setUser($user1);
        $notificationAnomalyRead->setMessage('Le rapport est disponible');
        $notificationAnomalyRead->setUrl('');
        $notificationAnomalyRead->setIsRead(true);
        $notificationAnomalyRead->setCreatedAt(new \DateTime('2020-03-29'));
        $notificationAnomalyRead->setUpdatedAt(new \DateTime('2020-03-30'));
        $manager->persist($notificationAnomalyRead);

        $notificationDashboardUnread = new Notification();
        $notificationDashboardUnread->setUser($user1);
        $notificationDashboardUnread->setMessage('Le rapport de l\'import est disponible');
        $notificationDashboardUnread->setUrl('');
        $notificationDashboardUnread->setData(['report_id' => $importReport3->getId(), 'report_type' => Import::TYPE_INVOICE]);
        $notificationDashboardUnread->setIsRead(false);
        $notificationDashboardUnread->setCreatedAt(new \DateTime('2020-04-15'));
        $notificationDashboardUnread->setUpdatedAt(null);
        $manager->persist($notificationDashboardUnread);

        $notificationDashboardRead = new Notification();
        $notificationDashboardRead->setUser($user2);
        $notificationDashboardRead->setMessage('Le rapport de l\'import est disponible');
        $notificationDashboardRead->setUrl('');
        $notificationDashboardRead->setIsRead(true);
        $notificationDashboardRead->setCreatedAt(new \DateTime('2020-04-21'));
        $notificationDashboardRead->setUpdatedAt(new \DateTime('2020-04-22'));
        $manager->persist($notificationDashboardRead);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ImportReportFixtures::class
        ];
    }
}
