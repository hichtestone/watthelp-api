<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201012081134 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE budget CHANGE total_hours total_hours INT DEFAULT NULL, CHANGE average_price average_price BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery_point_budget DROP FOREIGN KEY FK_85D4F90136ABA6B8');
        $this->addSql('ALTER TABLE delivery_point_budget DROP FOREIGN KEY FK_85D4F901A1492FCE');
        $this->addSql('ALTER TABLE delivery_point_budget ADD CONSTRAINT FK_85D4F90136ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE delivery_point_budget ADD CONSTRAINT FK_85D4F901A1492FCE FOREIGN KEY (delivery_point_id) REFERENCES delivery_point (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE budget CHANGE total_hours total_hours INT NOT NULL, CHANGE average_price average_price BIGINT NOT NULL');
        $this->addSql('ALTER TABLE delivery_point_budget DROP FOREIGN KEY FK_85D4F901A1492FCE');
        $this->addSql('ALTER TABLE delivery_point_budget DROP FOREIGN KEY FK_85D4F90136ABA6B8');
        $this->addSql('ALTER TABLE delivery_point_budget ADD CONSTRAINT FK_85D4F901A1492FCE FOREIGN KEY (delivery_point_id) REFERENCES delivery_point (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE delivery_point_budget ADD CONSTRAINT FK_85D4F90136ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
