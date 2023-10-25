<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version20201015065832 extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container = null;

    public function setContainer(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf(is_null($this->container), 'The container must be set to execute this migration');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        $deliveryPointBudgets = $entityManager->getConnection()->fetchAll(
            'SELECT dpb.id, dpb.renovation_month, b.year
                FROM delivery_point_budget dpb
                LEFT JOIN budget b ON dpb.budget_id = b.id
                WHERE renovation_month IS NOT NULL'
        );

        $this->addSql('ALTER TABLE delivery_point_budget ADD renovated_at DATETIME DEFAULT NULL, DROP renovation_month');

        foreach ($deliveryPointBudgets as $deliveryPointBudget) {
            [
                'id' => $id,
                'renovation_month' => $month,
                'year' => $year
            ] = $deliveryPointBudget;
            $renovatedAt = \DateTime::createFromFormat('Y-n-d', "$year-$month-01");
            if ($renovatedAt) {
                $renovatedAt = $renovatedAt->setTime(0, 0, 0)->format('c');
                $this->addSql('UPDATE delivery_point_budget SET renovated_at = :renovatedAt WHERE id = :id', ['renovatedAt' => $renovatedAt, 'id' => $id]);
            }
        }
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf(is_null($this->container), 'The container must be set to execute this migration');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        $deliveryPointBudgets = $entityManager->getConnection()->fetchAll(
            'SELECT id, renovated_at
                FROM delivery_point_budget
                WHERE renovated_at IS NOT NULL'
        );

        $this->addSql('ALTER TABLE delivery_point_budget ADD renovation_month INT DEFAULT NULL, DROP renovated_at');

        foreach ($deliveryPointBudgets as $deliveryPointBudget) {
            [
                'id' => $id,
                'renovated_at' => $renovatedAt,
            ] = $deliveryPointBudget;
            $renovatedAt = \DateTime::createFromFormat('Y-m-d H:i:s', $renovatedAt);
            if ($renovatedAt) {
                $this->addSql('UPDATE delivery_point_budget SET renovation_month = :renovationMonth WHERE id = :id', ['renovationMonth' => intval($renovatedAt->format('n')), 'id' => $id]);
            }
        }
    }
}
