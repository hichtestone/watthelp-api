<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210114141901 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // make sure column is still nullable for now
        $this->addSql('ALTER TABLE anomaly CHANGE profit profit ENUM(\'client\', \'provider\', \'none\') COMMENT \'(DC2Type:enumAnomalyProfit)\'');
        $this->addSql('UPDATE anomaly SET profit=\'none\' WHERE profit IS NULL');
        // now make column not nullable
        $this->addSql('ALTER TABLE anomaly CHANGE profit profit ENUM(\'client\', \'provider\', \'none\') COMMENT \'(DC2Type:enumAnomalyProfit)\' NOT NULL COMMENT \'(DC2Type:enumAnomalyProfit)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // make column nullable
        $this->addSql('ALTER TABLE anomaly CHANGE profit profit ENUM(\'client\', \'provider\', \'none\') COMMENT \'(DC2Type:enumAnomalyProfit)\' DEFAULT NULL COMMENT \'(DC2Type:enumAnomalyProfit)\'');
        $this->addSql('UPDATE anomaly SET profit=null WHERE profit = \'none\'');
        // remove none from enum
        $this->addSql('ALTER TABLE anomaly CHANGE profit profit ENUM(\'client\', \'provider\') COMMENT \'(DC2Type:enumAnomalyProfit)\' DEFAULT NULL COMMENT \'(DC2Type:enumAnomalyProfit)\'');
    }
}
