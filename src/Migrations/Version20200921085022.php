<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200921085022 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE anomaly DROP FOREIGN KEY FK_F9F975631613107C');
        $this->addSql('ALTER TABLE anomaly ADD CONSTRAINT FK_F9F975631613107C FOREIGN KEY (import_report_id) REFERENCES import_report (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64919EB6921');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64986383B10');
        $this->addSql('ALTER TABLE user CHANGE client_id client_id INT NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64919EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64986383B10 FOREIGN KEY (avatar_id) REFERENCES file (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE client DROP INDEX IDX_C7440455F98F144A, ADD UNIQUE INDEX UNIQ_C7440455F98F144A (logo_id)');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455F98F144A');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455F98F144A FOREIGN KEY (logo_id) REFERENCES file (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE anomaly DROP FOREIGN KEY FK_F9F975631613107C');
        $this->addSql('ALTER TABLE anomaly ADD CONSTRAINT FK_F9F975631613107C FOREIGN KEY (import_report_id) REFERENCES import_report (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE client DROP INDEX UNIQ_C7440455F98F144A, ADD INDEX IDX_C7440455F98F144A (logo_id)');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455F98F144A');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455F98F144A FOREIGN KEY (logo_id) REFERENCES file (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64986383B10');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64919EB6921');
        $this->addSql('ALTER TABLE user CHANGE client_id client_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64986383B10 FOREIGN KEY (avatar_id) REFERENCES file (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64919EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
