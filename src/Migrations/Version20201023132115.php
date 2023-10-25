<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201023132115 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE delivery_point_import_report (delivery_point_id INT NOT NULL, import_report_id INT NOT NULL, INDEX IDX_2780B056A1492FCE (delivery_point_id), INDEX IDX_2780B0561613107C (import_report_id), PRIMARY KEY(delivery_point_id, import_report_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget_import_report (budget_id INT NOT NULL, import_report_id INT NOT NULL, INDEX IDX_1A821EA836ABA6B8 (budget_id), INDEX IDX_1A821EA81613107C (import_report_id), PRIMARY KEY(budget_id, import_report_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE delivery_point_import_report ADD CONSTRAINT FK_2780B056A1492FCE FOREIGN KEY (delivery_point_id) REFERENCES delivery_point (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE delivery_point_import_report ADD CONSTRAINT FK_2780B0561613107C FOREIGN KEY (import_report_id) REFERENCES import_report (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE budget_import_report ADD CONSTRAINT FK_1A821EA836ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE budget_import_report ADD CONSTRAINT FK_1A821EA81613107C FOREIGN KEY (import_report_id) REFERENCES import_report (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE delivery_point DROP FOREIGN KEY FK_A7AE15B61613107C');
        $this->addSql('DROP INDEX IDX_A7AE15B61613107C ON delivery_point');
        $this->addSql('INSERT INTO delivery_point_import_report (delivery_point_id, import_report_id) SELECT id, import_report_id FROM delivery_point WHERE import_report_id IS NOT NULL');
        $this->addSql('ALTER TABLE delivery_point DROP import_report_id');
        $this->addSql('ALTER TABLE budget DROP FOREIGN KEY FK_73F2F77B1613107C');
        $this->addSql('DROP INDEX IDX_73F2F77B1613107C ON budget');
        $this->addSql('INSERT INTO budget_import_report (budget_id, import_report_id) SELECT id, import_report_id FROM budget WHERE import_report_id IS NOT NULL');
        $this->addSql('ALTER TABLE budget DROP import_report_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE delivery_point_import_report');
        $this->addSql('DROP TABLE budget_import_report');
        $this->addSql('ALTER TABLE budget ADD import_report_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B1613107C FOREIGN KEY (import_report_id) REFERENCES import_report (id)');
        $this->addSql('CREATE INDEX IDX_73F2F77B1613107C ON budget (import_report_id)');
        $this->addSql('ALTER TABLE delivery_point ADD import_report_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery_point ADD CONSTRAINT FK_A7AE15B61613107C FOREIGN KEY (import_report_id) REFERENCES import_report (id)');
        $this->addSql('CREATE INDEX IDX_A7AE15B61613107C ON delivery_point (import_report_id)');
    }
}
