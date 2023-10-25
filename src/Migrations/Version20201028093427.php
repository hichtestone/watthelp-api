<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201028093427 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM permission');
        $this->addSql('ALTER TABLE permission MODIFY code ENUM(\'user.view\', \'user.edit\', \'user.edit_password\', \'user.delete\', \'tax.view\', \'tax.edit\', \'tax.delete\', \'pricing.view\', \'pricing.edit\', \'pricing.delete\', \'role.view\', \'role.edit\', \'role.delete\', \'permission.view\', \'contract.view\', \'contract.edit\', \'contract.delete\', \'delivery_point.view\', \'delivery_point.edit\', \'delivery_point.delete\', \'delivery_point.map\', \'invoice.view\', \'invoice.edit\', \'invoice.delete\', \'invoice.analyze\', \'analysis.view\', \'budget.view\', \'budget.edit\', \'budget.delete\', \'anomaly.view\', \'anomaly.edit\', \'anomaly.note.view\', \'anomaly.note.edit\', \'export.budget\', \'export.anomaly\', \'import.budget\', \'import.invoice\', \'import.scope\', \'import.file\', \'import.report.view\', \'dashboard.view\')');
        $this->addSql('ALTER TABLE permission MODIFY group_name ENUM(\'system\', \'management\', \'other\')');

        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(1, \'user.view\', \'system\', \'Voir les utilisateurs\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(2, \'user.edit\', \'system\', \'Modifier un utilisateur\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(3, \'user.edit_password\', \'system\', \'Modifier le mot de passe d\'\'un utilisateur\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(4, \'user.delete\', \'system\', \'Supprimer un utilisateur\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(5, \'tax.view\', \'system\', \'Voir les taxes\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(6, \'tax.edit\', \'system\', \'Modifier une taxe\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(7, \'tax.delete\', \'system\', \'Supprimer une taxe\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(8, \'pricing.view\', \'system\', \'Voir les tarifs\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(9, \'pricing.edit\', \'system\', \'Modifier un tarif\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(10, \'pricing.delete\', \'system\', \'Supprimer un tarif\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(11, \'role.view\', \'system\', \'Voir les rôles\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(12, \'role.edit\', \'system\', \'Modifier un rôle\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(13, \'role.delete\', \'system\', \'Supprimer un rôle\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(14, \'permission.view\', \'system\', \'Voir les permissions\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(15, \'contract.view\', \'management\', \'Voir les contrats\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(16, \'contract.edit\', \'management\', \'Modifier un contrat\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(17, \'contract.delete\', \'management\', \'Supprimer un contrat\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(18, \'delivery_point.view\', \'management\', \'Voir les points de livraison\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(19, \'delivery_point.edit\', \'management\', \'Modifier un point de livraison\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(20, \'delivery_point.delete\', \'management\', \'Supprimer un point de livraison\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(21, \'delivery_point.map\', \'management\', \'Voir la cartographie\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(22, \'invoice.view\', \'management\', \'Voir les factures\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(23, \'invoice.edit\', \'management\', \'Modifier une facture\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(24, \'invoice.delete\', \'management\', \'Supprimer une facture\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(25, \'invoice.analyze\', \'management\', \'Analyser une facture\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(26, \'analysis.view\', \'management\', \'Voir les analyses\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(27, \'budget.view\', \'management\', \'Voir les budgets\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(28, \'budget.edit\', \'management\', \'Modifier un budget\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(29, \'budget.delete\', \'management\', \'Supprimer un budget\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(30, \'anomaly.view\', \'management\', \'Voir les alertes\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(31, \'anomaly.edit\', \'management\', \'Modifier une alerte\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(32, \'anomaly.note.view\', \'management\', \'Voir les commentaires\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(33, \'anomaly.note.edit\', \'management\', \'Modifier un commentaire\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(34, \'export.budget\', \'management\', \'Exporter des budgets\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(35, \'export.anomaly\', \'management\', \'Exporter des alertes\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(36, \'import.budget\', \'management\', \'Importer des budgets\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(37, \'import.invoice\', \'management\', \'Importer une facture\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(38, \'import.scope\', \'management\', \'Importer un périmètre\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(39, \'import.file\', \'management\', \'Télécharger un document d\'\'import\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(40, \'import.report.view\', \'management\', \'Voir les rapports d\'\'import\')');
        $this->addSql('INSERT INTO permission (id, code, group_name, description) VALUES(41, \'dashboard.view\', \'other\', \'Voir le dashboard\')');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM permission');
        $this->addSql('ALTER TABLE permission MODIFY code ENUM(\'permission_placeholder_1\', \'permission_placeholder_2\', \'permission_placeholder_3\', \'permission_placeholder_4\', \'permission_placeholder_5\')');

        $this->addSql('ALTER TABLE permission MODIFY group_name ENUM(\'group_placeholder_1\', \'group_placeholder_2\')');
    }
}
