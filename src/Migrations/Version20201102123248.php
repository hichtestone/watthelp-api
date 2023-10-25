<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201102123248 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM permission');
        $this->addSql('ALTER TABLE permission DROP COLUMN group_name');
        $this->addSql('ALTER TABLE permission MODIFY code ENUM(\'system.user.view\', \'system.user.edit\', \'system.user.edit_password\', \'system.user.delete\', \'system.tax.view\', \'system.tax.edit\', \'system.tax.delete\', \'system.pricing.view\', \'system.pricing.edit\', \'system.pricing.delete\', \'system.role.view\', \'system.role.edit\', \'system.role.delete\', \'system.permission.view\', \'management.contract.view\', \'management.contract.edit\', \'management.contract.delete\', \'management.delivery_point.view\', \'management.delivery_point.edit\', \'management.delivery_point.delete\', \'management.delivery_point.map\', \'management.invoice.view\', \'management.invoice.edit\', \'management.invoice.delete\', \'management.invoice.analyze\', \'management.analysis.view\', \'management.budget.view\', \'management.budget.edit\', \'management.budget.delete\', \'management.anomaly.view\', \'management.anomaly.edit\', \'management.anomaly.note.view\', \'management.anomaly.note.edit\', \'management.export.budget\', \'management.export.anomaly\', \'management.import.budget\', \'management.import.invoice\', \'management.import.scope\', \'management.import.file\', \'management.import.report.view\', \'other.dashboard.view\')');

        $this->addSql('INSERT INTO permission (id, code, description) VALUES(1, \'system.user.view\', \'Voir les utilisateurs\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(2, \'system.user.edit\', \'Modifier un utilisateur\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(3, \'system.user.edit_password\', \'Modifier le mot de passe d\'\'un utilisateur\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(4, \'system.user.delete\', \'Supprimer un utilisateur\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(5, \'system.tax.view\', \'Voir les taxes\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(6, \'system.tax.edit\', \'Modifier une taxe\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(7, \'system.tax.delete\', \'Supprimer une taxe\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(8, \'system.pricing.view\', \'Voir les tarifs\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(9, \'system.pricing.edit\', \'Modifier un tarif\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(10, \'system.pricing.delete\', \'Supprimer un tarif\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(11, \'system.role.view\', \'Voir les rôles\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(12, \'system.role.edit\', \'Modifier un rôle\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(13, \'system.role.delete\', \'Supprimer un rôle\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(14, \'system.permission.view\', \'Voir les permissions\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(15, \'management.contract.view\', \'Voir les contrats\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(16, \'management.contract.edit\', \'Modifier un contrat\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(17, \'management.contract.delete\', \'Supprimer un contrat\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(18, \'management.delivery_point.view\', \'Voir les points de livraison\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(19, \'management.delivery_point.edit\', \'Modifier un point de livraison\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(20, \'management.delivery_point.delete\', \'Supprimer un point de livraison\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(21, \'management.delivery_point.map\', \'Voir la cartographie\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(22, \'management.invoice.view\', \'Voir les factures\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(23, \'management.invoice.edit\', \'Modifier une facture\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(24, \'management.invoice.delete\', \'Supprimer une facture\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(25, \'management.invoice.analyze\', \'Analyser une facture\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(26, \'management.analysis.view\', \'Voir les analyses\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(27, \'management.budget.view\', \'Voir les budgets\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(28, \'management.budget.edit\', \'Modifier un budget\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(29, \'management.budget.delete\', \'Supprimer un budget\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(30, \'management.anomaly.view\', \'Voir les alertes\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(31, \'management.anomaly.edit\', \'Modifier une alerte\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(32, \'management.anomaly.note.view\', \'Voir les commentaires\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(33, \'management.anomaly.note.edit\', \'Modifier un commentaire\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(34, \'management.export.budget\', \'Exporter des budgets\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(35, \'management.export.anomaly\', \'Exporter des alertes\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(36, \'management.import.budget\', \'Importer des budgets\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(37, \'management.import.invoice\', \'Importer une facture\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(38, \'management.import.scope\', \'Importer un périmètre\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(39, \'management.import.file\', \'Télécharger un document d\'\'import\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(40, \'management.import.report.view\', \'Voir les rapports d\'\'import\')');
        $this->addSql('INSERT INTO permission (id, code, description) VALUES(41, \'other.dashboard.view\', \'Voir le dashboard\')');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
