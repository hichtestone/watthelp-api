<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201117094117 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD language ENUM(\'fr\', \'en\') COMMENT \'(DC2Type:enumTypeLanguage)\' NOT NULL COMMENT \'(DC2Type:enumTypeLanguage)\'');
        $this->addSql('ALTER TABLE client ADD default_language ENUM(\'fr\', \'en\') COMMENT \'(DC2Type:enumTypeLanguage)\' NOT NULL COMMENT \'(DC2Type:enumTypeLanguage)\'');
        $this->addSql('UPDATE user SET language = \'fr\'');
        $this->addSql('UPDATE client SET default_language = \'fr\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE client DROP default_language');
        $this->addSql('ALTER TABLE user DROP language');
    }
}