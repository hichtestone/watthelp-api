<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210114121843 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // add analysis delete permission
        $analysisDeletePermission = 'management.analysis.delete';
        $analysisDeletePermissionDescription = 'Supprimer les analyses';
        $permissions = "system.user.view','system.user.edit','system.user.edit_password','system.user.delete','system.tax.view','system.tax.edit','system.tax.delete','system.pricing.view','system.pricing.edit','system.pricing.delete','system.role.view','system.role.edit','system.role.delete','system.permission.view','management.contract.view','management.contract.edit','management.contract.delete','management.delivery_point.view','management.delivery_point.edit','management.delivery_point.delete','management.delivery_point.map','management.invoice.view','management.invoice.edit','management.invoice.delete','management.invoice.analyze','management.analysis.view','management.analysis.delete','management.budget.view','management.budget.edit','management.budget.delete','management.anomaly.view','management.anomaly.edit','management.anomaly.delete','management.anomaly_note.edit','management.export.budget','management.export.anomaly','management.export.delivery_point','management.import.budget','management.import.invoice','management.import.scope','management.import.file','management.import_report.view','other.dashboard.view";
        $this->addSql("ALTER TABLE permission MODIFY code ENUM('$permissions')");
        $this->addSql("INSERT INTO permission (code, description) VALUES('$analysisDeletePermission', '$analysisDeletePermissionDescription')");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // delete analysis delete permission
        $analysisDeletePermission = 'management.analysis.delete';
        $permissions = "system.user.view','system.user.edit','system.user.edit_password','system.user.delete','system.tax.view','system.tax.edit','system.tax.delete','system.pricing.view','system.pricing.edit','system.pricing.delete','system.role.view','system.role.edit','system.role.delete','system.permission.view','management.contract.view','management.contract.edit','management.contract.delete','management.delivery_point.view','management.delivery_point.edit','management.delivery_point.delete','management.delivery_point.map','management.invoice.view','management.invoice.edit','management.invoice.delete','management.invoice.analyze','management.analysis.view','management.budget.view','management.budget.edit','management.budget.delete','management.anomaly.view','management.anomaly.edit','management.anomaly.delete','management.anomaly_note.edit','management.export.budget','management.export.anomaly','management.export.delivery_point','management.import.budget','management.import.invoice','management.import.scope','management.import.file','management.import_report.view','other.dashboard.view";
        $this->addSql("DELETE FROM permission WHERE code = '$analysisDeletePermission'");
        $this->addSql("ALTER TABLE permission MODIFY code ENUM('$permissions')");
    }
}
