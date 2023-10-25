<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200924090318 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE budget (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, year INT NOT NULL, total_hours INT NOT NULL, average_price BIGINT NOT NULL, total_consumption INT DEFAULT NULL, total_amount BIGINT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_73F2F77B19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE delivery_point_budget (id INT AUTO_INCREMENT NOT NULL, delivery_point_id INT NOT NULL, budget_id INT NOT NULL, installed_power NUMERIC(10, 2) DEFAULT NULL, equipment_power_percentage INT DEFAULT NULL, gradation INT DEFAULT NULL, gradation_hours INT DEFAULT NULL, sub_total_consumption INT DEFAULT NULL, renovation TINYINT(1) NOT NULL, renovation_month INT DEFAULT NULL, new_installed_power NUMERIC(10, 2) DEFAULT NULL, new_equipment_power_percentage INT DEFAULT NULL, new_gradation INT DEFAULT NULL, new_gradation_hours INT DEFAULT NULL, new_sub_total_consumption INT DEFAULT NULL, total_consumption INT DEFAULT NULL, total BIGINT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_85D4F901A1492FCE (delivery_point_id), INDEX IDX_85D4F90136ABA6B8 (budget_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE delivery_point_budget ADD CONSTRAINT FK_85D4F901A1492FCE FOREIGN KEY (delivery_point_id) REFERENCES delivery_point (id)');
        $this->addSql('ALTER TABLE delivery_point_budget ADD CONSTRAINT FK_85D4F90136ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE delivery_point_budget DROP FOREIGN KEY FK_85D4F90136ABA6B8');
        $this->addSql('DROP TABLE budget');
        $this->addSql('DROP TABLE delivery_point_budget');
    }
}
