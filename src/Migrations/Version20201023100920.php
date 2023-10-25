<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201023100920 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE permission (id INT AUTO_INCREMENT NOT NULL, code ENUM(\'permission_placeholder_1\', \'permission_placeholder_2\', \'permission_placeholder_3\', \'permission_placeholder_4\', \'permission_placeholder_5\') COMMENT \'(DC2Type:enumTypePermissionCode)\' NOT NULL COMMENT \'(DC2Type:enumTypePermissionCode)\', group_name ENUM(\'group_placeholder_1\', \'group_placeholder_2\') COMMENT \'(DC2Type:enumTypePermissionGroup)\' NOT NULL COMMENT \'(DC2Type:enumTypePermissionGroup)\', description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_E04992AA77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission_role (permission_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_6A711CAFED90CCA (permission_id), INDEX IDX_6A711CAD60322AC (role_id), PRIMARY KEY(permission_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE permission_role ADD CONSTRAINT FK_6A711CAFED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE permission_role ADD CONSTRAINT FK_6A711CAD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE permission_role DROP FOREIGN KEY FK_6A711CAFED90CCA');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE permission_role');
    }
}
