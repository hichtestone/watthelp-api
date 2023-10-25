<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201001134250 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE import MODIFY type ENUM(\'invoice\', \'scope\', \'budget\')');
        $this->addSql('ALTER TABLE budget ADD import_report_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B1613107C FOREIGN KEY (import_report_id) REFERENCES import_report (id)');
        $this->addSql('CREATE INDEX IDX_73F2F77B1613107C ON budget (import_report_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE import MODIFY type ENUM(\'invoice\', \'scope\')');
        $this->addSql('ALTER TABLE budget DROP FOREIGN KEY FK_73F2F77B1613107C');
        $this->addSql('DROP INDEX IDX_73F2F77B1613107C ON budget');
        $this->addSql('ALTER TABLE budget DROP import_report_id');
    }
}