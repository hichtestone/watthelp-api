<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Permission;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201215100544 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // add delivery point export permission
        $dpExportPermission = Permission::EXPORT_DELIVERY_POINT;
        $dpExportPermissionDescription = Permission::AVAILABLE_PERMISSIONS[$dpExportPermission]['description'];
        $permissions = implode("','", Permission::AVAILABLE_PERMISSION_CODES);
        $this->addSql("ALTER TABLE permission MODIFY code ENUM('$permissions')");
        $this->addSql("INSERT INTO permission (code, description) VALUES('$dpExportPermission', '$dpExportPermissionDescription')");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // delete delivery point export permission
        $dpExportPermission = Permission::EXPORT_DELIVERY_POINT;
        $permissions = Permission::AVAILABLE_PERMISSION_CODES;
        unset($permissions[$dpExportPermission]);
        $permissions = implode("','", $permissions);
        $this->addSql("DELETE FROM permission WHERE code = '$dpExportPermission'");
        $this->addSql("ALTER TABLE permission MODIFY code ENUM('$permissions')");
    }
}
