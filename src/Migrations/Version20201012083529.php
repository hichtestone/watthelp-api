<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201012083529 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE delivery_point_invoice DROP FOREIGN KEY FK_2043A9682989F1FD');
        $this->addSql('ALTER TABLE delivery_point_invoice DROP FOREIGN KEY FK_2043A968A1492FCE');
        $this->addSql('ALTER TABLE delivery_point_invoice ADD CONSTRAINT FK_2043A9682989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE delivery_point_invoice ADD CONSTRAINT FK_2043A968A1492FCE FOREIGN KEY (delivery_point_id) REFERENCES delivery_point (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE analysis DROP FOREIGN KEY FK_33C7302989F1FD');
        $this->addSql('ALTER TABLE analysis ADD CONSTRAINT FK_33C7302989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE delivery_point_invoice_analysis DROP FOREIGN KEY FK_E053799B0954802');
        $this->addSql('ALTER TABLE delivery_point_invoice_analysis ADD CONSTRAINT FK_E053799B0954802 FOREIGN KEY (delivery_point_invoice_id) REFERENCES delivery_point_invoice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice_consumption DROP FOREIGN KEY FK_3BB14FCAB0954802');
        $this->addSql('ALTER TABLE invoice_consumption ADD CONSTRAINT FK_3BB14FCAB0954802 FOREIGN KEY (delivery_point_invoice_id) REFERENCES delivery_point_invoice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice_subscription DROP FOREIGN KEY FK_1C014BA7B0954802');
        $this->addSql('ALTER TABLE invoice_subscription ADD CONSTRAINT FK_1C014BA7B0954802 FOREIGN KEY (delivery_point_invoice_id) REFERENCES delivery_point_invoice (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE analysis DROP FOREIGN KEY FK_33C7302989F1FD');
        $this->addSql('ALTER TABLE analysis ADD CONSTRAINT FK_33C7302989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE delivery_point_invoice DROP FOREIGN KEY FK_2043A968A1492FCE');
        $this->addSql('ALTER TABLE delivery_point_invoice DROP FOREIGN KEY FK_2043A9682989F1FD');
        $this->addSql('ALTER TABLE delivery_point_invoice ADD CONSTRAINT FK_2043A968A1492FCE FOREIGN KEY (delivery_point_id) REFERENCES delivery_point (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE delivery_point_invoice ADD CONSTRAINT FK_2043A9682989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE delivery_point_invoice_analysis DROP FOREIGN KEY FK_E053799B0954802');
        $this->addSql('ALTER TABLE delivery_point_invoice_analysis ADD CONSTRAINT FK_E053799B0954802 FOREIGN KEY (delivery_point_invoice_id) REFERENCES delivery_point_invoice (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE invoice_consumption DROP FOREIGN KEY FK_3BB14FCAB0954802');
        $this->addSql('ALTER TABLE invoice_consumption ADD CONSTRAINT FK_3BB14FCAB0954802 FOREIGN KEY (delivery_point_invoice_id) REFERENCES delivery_point_invoice (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE invoice_subscription DROP FOREIGN KEY FK_1C014BA7B0954802');
        $this->addSql('ALTER TABLE invoice_subscription ADD CONSTRAINT FK_1C014BA7B0954802 FOREIGN KEY (delivery_point_invoice_id) REFERENCES delivery_point_invoice (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
