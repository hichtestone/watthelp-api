<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Permission;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210211174057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // add pricing import permission
        $pricingImportPermission = Permission::PRICING_IMPORT;
        $pricingImportPermissionDescription = Permission::AVAILABLE_PERMISSIONS[$pricingImportPermission]['description'];

        $this->addSql('ALTER TABLE permission MODIFY code ENUM(\'system.user.view\',\'system.user.edit\',\'system.user.edit_password\',\'system.user.delete\',\'system.tax.view\',\'system.tax.edit\',\'system.tax.delete\',\'system.pricing.view\',\'system.pricing.edit\',\'system.pricing.delete\',\'system.role.view\',\'system.role.edit\',\'system.role.delete\',\'system.permission.view\',\'management.contract.view\',\'management.contract.edit\',\'management.contract.delete\',\'management.delivery_point.view\',\'management.delivery_point.edit\',\'management.delivery_point.delete\',\'management.delivery_point.map\',\'management.invoice.view\',\'management.invoice.edit\',\'management.invoice.delete\',\'management.invoice.analyze\',\'management.analysis.view\',\'management.analysis.delete\',\'management.budget.view\',\'management.budget.edit\',\'management.budget.delete\',\'management.anomaly.view\',\'management.anomaly.edit\',\'management.anomaly.delete\',\'management.anomaly_note.edit\',\'management.export.budget\',\'management.export.anomaly\',\'management.export.delivery_point\',\'management.import.budget\',\'management.import.invoice\',\'management.import.scope\',\'management.import.file\',\'management.import_report.view\',\'system.export.pricing\',\'other.dashboard.view\',\'system.pricing.import\')');
        $this->addSql("INSERT INTO permission (code, description) VALUES('$pricingImportPermission','$pricingImportPermissionDescription')");
        $this->addSql('ALTER TABLE permission MODIFY code ENUM(\'system.user.view\',\'system.user.edit\',\'system.user.edit_password\',\'system.user.delete\',\'system.tax.view\',\'system.tax.edit\',\'system.tax.delete\',\'system.pricing.view\',\'system.pricing.edit\',\'system.pricing.delete\',\'system.role.view\',\'system.role.edit\',\'system.role.delete\',\'system.permission.view\',\'management.contract.view\',\'management.contract.edit\',\'management.contract.delete\',\'management.delivery_point.view\',\'management.delivery_point.edit\',\'management.delivery_point.delete\',\'management.delivery_point.map\',\'management.invoice.view\',\'management.invoice.edit\',\'management.invoice.delete\',\'management.invoice.analyze\',\'management.analysis.view\',\'management.analysis.delete\',\'management.budget.view\',\'management.budget.edit\',\'management.budget.delete\',\'management.anomaly.view\',\'management.anomaly.edit\',\'management.anomaly.delete\',\'management.anomaly_note.edit\',\'management.export.budget\',\'management.export.anomaly\',\'management.export.delivery_point\',\'management.import.budget\',\'management.import.invoice\',\'management.import.scope\',\'management.import.file\',\'management.import_report.view\',\'system.export.pricing\',\'system.pricing.export\',\'other.dashboard.view\',\'system.pricing.import\')');

        // update pricing export permission
        $this->addSql('UPDATE permission SET code=\'system.pricing.export\' WHERE code=\'system.export.pricing\'');
        $this->addSql('ALTER TABLE permission MODIFY code ENUM(\'system.user.view\',\'system.user.edit\',\'system.user.edit_password\',\'system.user.delete\',\'system.tax.view\',\'system.tax.edit\',\'system.tax.delete\',\'system.pricing.view\',\'system.pricing.edit\',\'system.pricing.delete\',\'system.role.view\',\'system.role.edit\',\'system.role.delete\',\'system.permission.view\',\'management.contract.view\',\'management.contract.edit\',\'management.contract.delete\',\'management.delivery_point.view\',\'management.delivery_point.edit\',\'management.delivery_point.delete\',\'management.delivery_point.map\',\'management.invoice.view\',\'management.invoice.edit\',\'management.invoice.delete\',\'management.invoice.analyze\',\'management.analysis.view\',\'management.analysis.delete\',\'management.budget.view\',\'management.budget.edit\',\'management.budget.delete\',\'management.anomaly.view\',\'management.anomaly.edit\',\'management.anomaly.delete\',\'management.anomaly_note.edit\',\'management.export.budget\',\'management.export.anomaly\',\'management.export.delivery_point\',\'management.import.budget\',\'management.import.invoice\',\'management.import.scope\',\'management.import.file\',\'management.import_report.view\',\'system.pricing.export\',\'other.dashboard.view\',\'system.pricing.import\')');

        // add import type pricing
        $this->addSql('ALTER TABLE import MODIFY type ENUM(\'invoice\', \'scope\', \'budget\', \'pricing\')');

        // ManyToMany ORM between pricing and import_report tables
        $this->addSql('CREATE TABLE pricing_import_report (pricing_id INT NOT NULL, import_report_id INT NOT NULL,  INDEX IDX_C01D965A8864AF73 (pricing_id), INDEX IDX_C01D965A1613107C (import_report_id), PRIMARY KEY(pricing_id, import_report_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE pricing_import_report ADD CONSTRAINT FK_C01D965A1613107C FOREIGN KEY (import_report_id) REFERENCES import_report (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pricing_import_report ADD CONSTRAINT FK_C01D965A8864AF73 FOREIGN KEY (pricing_id) REFERENCES pricing (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // remove import type pricing
        $this->addSql('ALTER TABLE import MODIFY type ENUM(\'invoice\', \'scope\', \'budget\')');

        // delete pricing import permission
        $pricingImportPermission = Permission::PRICING_IMPORT;
        $permissions = Permission::AVAILABLE_PERMISSION_CODES;
        unset($permissions[$pricingImportPermission]);
        $permissions = implode("','", $permissions);

        $this->addSql("DELETE FROM permission WHERE code = '$pricingImportPermission'");
        $this->addSql("ALTER TABLE permission MODIFY code ENUM('$permissions')");

        //revert update of export pricing
        $this->addSql('UPDATE permission SET code=\'system.export.pricing\' WHERE code=\'system.pricing.export\'');
        $this->addSql('ALTER TABLE permission MODIFY code ENUM(\'system.user.view\',\'system.user.edit\',\'system.user.edit_password\',\'system.user.delete\',\'system.tax.view\',\'system.tax.edit\',\'system.tax.delete\',\'system.pricing.view\',\'system.pricing.edit\',\'system.pricing.delete\',\'system.role.view\',\'system.role.edit\',\'system.role.delete\',\'system.permission.view\',\'management.contract.view\',\'management.contract.edit\',\'management.contract.delete\',\'management.delivery_point.view\',\'management.delivery_point.edit\',\'management.delivery_point.delete\',\'management.delivery_point.map\',\'management.invoice.view\',\'management.invoice.edit\',\'management.invoice.delete\',\'management.invoice.analyze\',\'management.analysis.view\',\'management.analysis.delete\',\'management.budget.view\',\'management.budget.edit\',\'management.budget.delete\',\'management.anomaly.view\',\'management.anomaly.edit\',\'management.anomaly.delete\',\'management.anomaly_note.edit\',\'management.export.budget\',\'management.export.anomaly\',\'management.export.delivery_point\',\'management.import.budget\',\'management.import.invoice\',\'management.import.scope\',\'management.import.file\',\'management.import_report.view\',\'system.export.pricing\',\'other.dashboard.view\')');

        // Dropping pricing_import_report table
        $this->addSql('DROP TABLE pricing_import_report');
    }
}
