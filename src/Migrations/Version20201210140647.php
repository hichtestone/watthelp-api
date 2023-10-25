<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201210140647 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_A7AE15B677153098 ON delivery_point');
        $this->addSql('DROP INDEX UNIQ_A7AE15B6AEA34913 ON delivery_point');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A7AE15B619EB6921AEA34913 ON delivery_point (client_id, reference)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A7AE15B619EB692177153098 ON delivery_point (client_id, code)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_A7AE15B619EB6921AEA34913 ON delivery_point');
        $this->addSql('DROP INDEX UNIQ_A7AE15B619EB692177153098 ON delivery_point');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A7AE15B677153098 ON delivery_point (code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A7AE15B6AEA34913 ON delivery_point (reference)');
    }
}
