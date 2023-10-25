<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200921084354 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice_tax (id INT AUTO_INCREMENT NOT NULL, type ENUM(\'cspe\', \'tdcfe\', \'tccfe\', \'cta\', \'tcfe\') COMMENT \'(DC2Type:enumTypeInvoiceTaxType)\' NOT NULL COMMENT \'(DC2Type:enumTypeInvoiceTaxType)\', quantity INT DEFAULT NULL, unit_price INT DEFAULT NULL, total BIGINT DEFAULT NULL, started_at DATETIME DEFAULT NULL, finished_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, avatar_id INT DEFAULT NULL, client_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, mobile VARCHAR(20) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, connected_at DATETIME DEFAULT NULL, dashboard JSON DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D64986383B10 (avatar_id), INDEX IDX_8D93D64919EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, message LONGTEXT NOT NULL, progress INT DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, is_read TINYINT(1) DEFAULT \'0\', created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, data JSON DEFAULT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, raw VARCHAR(255) NOT NULL, thumb VARCHAR(255) NOT NULL, mime VARCHAR(255) DEFAULT \'text/plain\' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_8C9F3610A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, logo_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, zip_code VARCHAR(6) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, department VARCHAR(3) DEFAULT NULL, insee VARCHAR(255) DEFAULT NULL, INDEX IDX_C7440455F98F144A (logo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE import (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, file_id INT NOT NULL, type ENUM(\'invoice\', \'scope\') COMMENT \'(DC2Type:enumTypeImportType)\' NOT NULL COMMENT \'(DC2Type:enumTypeImportType)\', provider VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_9D4ECE1DA76ED395 (user_id), INDEX IDX_9D4ECE1D93CB796C (file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tax (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, cspe INT NOT NULL, tdcfe INT NOT NULL, tccfe INT NOT NULL, cta INT NOT NULL, started_at DATETIME NOT NULL, finished_at DATETIME NOT NULL, INDEX IDX_8E81BA7619EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pricing (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, name VARCHAR(255) NOT NULL, type ENUM(\'negotiated\', \'regulated\') COMMENT \'(DC2Type:enumTypeType)\' NOT NULL COMMENT \'(DC2Type:enumTypeType)\', subscription_price INT DEFAULT NULL, consumption_base_price INT NOT NULL, started_at DATETIME NOT NULL, finished_at DATETIME DEFAULT NULL, INDEX IDX_E5F1AC3319EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE import_report (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, import_id INT NOT NULL, status ENUM(\'ok\', \'error\', \'warning\') COMMENT \'(DC2Type:enumTypeImportReportStatus)\' NOT NULL COMMENT \'(DC2Type:enumTypeImportReportStatus)\', messages LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, INDEX IDX_B81ECD9CA76ED395 (user_id), UNIQUE INDEX UNIQ_B81ECD9CB6A263D9 (import_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, import_report_id INT DEFAULT NULL, pdf_id INT DEFAULT NULL, reference VARCHAR(255) NOT NULL, amount_ht BIGINT NOT NULL, amount_tva BIGINT NOT NULL, amount_ttc BIGINT NOT NULL, emitted_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_90651744AEA34913 (reference), INDEX IDX_9065174419EB6921 (client_id), INDEX IDX_906517441613107C (import_report_id), INDEX IDX_90651744511FC912 (pdf_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE analysis (id INT AUTO_INCREMENT NOT NULL, invoice_id INT NOT NULL, status ENUM(\'error\', \'ok\', \'processing\', \'warning\') COMMENT \'(DC2Type:enumTypeAnalysisStatus)\' NOT NULL COMMENT \'(DC2Type:enumTypeAnalysisStatus)\', created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_33C7302989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, reference VARCHAR(255) NOT NULL, provider ENUM(\'DIRECT_ENERGIE\', \'EDF\', \'ENGIE\', \'OTHER\') COMMENT \'(DC2Type:enumTypeProvider)\' NOT NULL COMMENT \'(DC2Type:enumTypeProvider)\', type ENUM(\'negotiated\', \'regulated\') COMMENT \'(DC2Type:enumTypeType)\' NOT NULL COMMENT \'(DC2Type:enumTypeType)\', invoice_period ENUM(\'1\', \'2\', \'6\', \'12\') COMMENT \'(DC2Type:enumInvoicePeriod)\' DEFAULT NULL COMMENT \'(DC2Type:enumInvoicePeriod)\', started_at DATETIME NOT NULL, finished_at DATETIME DEFAULT NULL, INDEX IDX_E98F285919EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE delivery_point (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, contract_id INT DEFAULT NULL, photo_id INT DEFAULT NULL, import_report_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, reference VARCHAR(255) NOT NULL, code VARCHAR(255) DEFAULT NULL, address VARCHAR(255) NOT NULL, latitude VARCHAR(255) DEFAULT NULL, longitude VARCHAR(255) DEFAULT NULL, meter_reference VARCHAR(255) NOT NULL, power NUMERIC(5, 1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, is_in_scope TINYINT(1) NOT NULL, scope_date DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_A7AE15B6AEA34913 (reference), UNIQUE INDEX UNIQ_A7AE15B677153098 (code), INDEX IDX_A7AE15B619EB6921 (client_id), INDEX IDX_A7AE15B62576E0FD (contract_id), INDEX IDX_A7AE15B67E9E4C8C (photo_id), INDEX IDX_A7AE15B61613107C (import_report_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE delivery_point_invoice (id INT AUTO_INCREMENT NOT NULL, delivery_point_id INT NOT NULL, invoice_id INT NOT NULL, amount_ht BIGINT NOT NULL, amount_tva BIGINT NOT NULL, amount_ttc BIGINT NOT NULL, power_subscribed NUMERIC(5, 1) DEFAULT NULL, type ENUM(\'estimated\', \'real\') COMMENT \'(DC2Type:enumTypeDeliveryPointInvoiceType)\' NOT NULL COMMENT \'(DC2Type:enumTypeDeliveryPointInvoiceType)\', INDEX IDX_2043A968A1492FCE (delivery_point_id), INDEX IDX_2043A9682989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice_subscription (id INT AUTO_INCREMENT NOT NULL, delivery_point_invoice_id INT NOT NULL, total BIGINT DEFAULT NULL, quantity INT DEFAULT NULL, unit_price INT DEFAULT NULL, started_at DATETIME DEFAULT NULL, finished_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1C014BA7B0954802 (delivery_point_invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice_consumption (id INT AUTO_INCREMENT NOT NULL, delivery_point_invoice_id INT NOT NULL, index_start INT DEFAULT NULL, index_started_at DATETIME DEFAULT NULL, index_finish INT DEFAULT NULL, index_finished_at DATETIME DEFAULT NULL, started_at DATETIME DEFAULT NULL, finished_at DATETIME DEFAULT NULL, quantity INT DEFAULT NULL, unit_price INT DEFAULT NULL, total BIGINT DEFAULT NULL, UNIQUE INDEX UNIQ_3BB14FCAB0954802 (delivery_point_invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE delivery_point_invoice_analysis (id INT AUTO_INCREMENT NOT NULL, analysis_id INT NOT NULL, delivery_point_invoice_id INT NOT NULL, previous_delivery_point_invoice_id INT DEFAULT NULL, status ENUM(\'error\', \'ok\', \'processing\', \'warning\') COMMENT \'(DC2Type:enumTypeAnalysisStatus)\' NOT NULL COMMENT \'(DC2Type:enumTypeAnalysisStatus)\', INDEX IDX_E0537997941003F (analysis_id), UNIQUE INDEX UNIQ_E053799B0954802 (delivery_point_invoice_id), UNIQUE INDEX UNIQ_E053799B4C25520 (previous_delivery_point_invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item_analysis (id INT AUTO_INCREMENT NOT NULL, analysis_id INT DEFAULT NULL, delivery_point_invoice_analysis_id INT DEFAULT NULL, anomaly_id INT DEFAULT NULL, analyzer VARCHAR(255) DEFAULT NULL, group_name VARCHAR(50) DEFAULT NULL, status ENUM(\'error\', \'ok\', \'processing\', \'warning\') COMMENT \'(DC2Type:enumTypeAnalysisStatus)\' NOT NULL COMMENT \'(DC2Type:enumTypeAnalysisStatus)\', messages TEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', field VARCHAR(255) DEFAULT NULL, INDEX IDX_6C3E0FFB7941003F (analysis_id), INDEX IDX_6C3E0FFB74EE12FA (delivery_point_invoice_analysis_id), UNIQUE INDEX UNIQ_6C3E0FFBBAF977BB (anomaly_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE anomaly (id INT AUTO_INCREMENT NOT NULL, item_analysis_id INT DEFAULT NULL, import_report_id INT DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, applied_rules VARCHAR(255) DEFAULT NULL, old_value VARCHAR(255) DEFAULT NULL, current_value VARCHAR(255) DEFAULT NULL, expected_value VARCHAR(255) DEFAULT NULL, type ENUM(\'subscription\', \'consumption\', \'turpe\', \'date\', \'index\', \'unit_price\', \'amount\', \'delivery_point_change\') COMMENT \'(DC2Type:enumTypeAnomalyType)\' NOT NULL COMMENT \'(DC2Type:enumTypeAnomalyType)\', status ENUM(\'solved\', \'unsolved\', \'processing\', \'ignored\') COMMENT \'(DC2Type:enumTypeAnomalyStatus)\' NOT NULL COMMENT \'(DC2Type:enumTypeAnomalyStatus)\', content LONGTEXT NOT NULL, total BIGINT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_F9F97563AEA34913 (reference), UNIQUE INDEX UNIQ_F9F9756399295809 (item_analysis_id), INDEX IDX_F9F975631613107C (import_report_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note (id INT AUTO_INCREMENT NOT NULL, anomaly_id INT NOT NULL, user_id INT NOT NULL, content VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_CFBDFA14BAF977BB (anomaly_id), INDEX IDX_CFBDFA14A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice_tax_delivery_point_invoice (invoice_tax_id INT NOT NULL, delivery_point_invoice_id INT NOT NULL, INDEX IDX_CA91E35999EB98B (invoice_tax_id), INDEX IDX_CA91E359B0954802 (delivery_point_invoice_id), PRIMARY KEY(invoice_tax_id, delivery_point_invoice_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract_pricing (contract_id INT NOT NULL, pricing_id INT NOT NULL, INDEX IDX_EDA0D1FB2576E0FD (contract_id), INDEX IDX_EDA0D1FB8864AF73 (pricing_id), PRIMARY KEY(contract_id, pricing_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64986383B10 FOREIGN KEY (avatar_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64919EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455F98F144A FOREIGN KEY (logo_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT FK_9D4ECE1DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT FK_9D4ECE1D93CB796C FOREIGN KEY (file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE tax ADD CONSTRAINT FK_8E81BA7619EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE pricing ADD CONSTRAINT FK_E5F1AC3319EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE import_report ADD CONSTRAINT FK_B81ECD9CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE import_report ADD CONSTRAINT FK_B81ECD9CB6A263D9 FOREIGN KEY (import_id) REFERENCES import (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174419EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517441613107C FOREIGN KEY (import_report_id) REFERENCES import_report (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744511FC912 FOREIGN KEY (pdf_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE analysis ADD CONSTRAINT FK_33C7302989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F285919EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE delivery_point ADD CONSTRAINT FK_A7AE15B619EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE delivery_point ADD CONSTRAINT FK_A7AE15B62576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE delivery_point ADD CONSTRAINT FK_A7AE15B67E9E4C8C FOREIGN KEY (photo_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE delivery_point ADD CONSTRAINT FK_A7AE15B61613107C FOREIGN KEY (import_report_id) REFERENCES import_report (id)');
        $this->addSql('ALTER TABLE delivery_point_invoice ADD CONSTRAINT FK_2043A968A1492FCE FOREIGN KEY (delivery_point_id) REFERENCES delivery_point (id)');
        $this->addSql('ALTER TABLE delivery_point_invoice ADD CONSTRAINT FK_2043A9682989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE invoice_subscription ADD CONSTRAINT FK_1C014BA7B0954802 FOREIGN KEY (delivery_point_invoice_id) REFERENCES delivery_point_invoice (id)');
        $this->addSql('ALTER TABLE invoice_consumption ADD CONSTRAINT FK_3BB14FCAB0954802 FOREIGN KEY (delivery_point_invoice_id) REFERENCES delivery_point_invoice (id)');
        $this->addSql('ALTER TABLE delivery_point_invoice_analysis ADD CONSTRAINT FK_E0537997941003F FOREIGN KEY (analysis_id) REFERENCES analysis (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE delivery_point_invoice_analysis ADD CONSTRAINT FK_E053799B0954802 FOREIGN KEY (delivery_point_invoice_id) REFERENCES delivery_point_invoice (id)');
        $this->addSql('ALTER TABLE delivery_point_invoice_analysis ADD CONSTRAINT FK_E053799B4C25520 FOREIGN KEY (previous_delivery_point_invoice_id) REFERENCES delivery_point_invoice (id)');
        $this->addSql('ALTER TABLE item_analysis ADD CONSTRAINT FK_6C3E0FFB7941003F FOREIGN KEY (analysis_id) REFERENCES analysis (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_analysis ADD CONSTRAINT FK_6C3E0FFB74EE12FA FOREIGN KEY (delivery_point_invoice_analysis_id) REFERENCES delivery_point_invoice_analysis (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_analysis ADD CONSTRAINT FK_6C3E0FFBBAF977BB FOREIGN KEY (anomaly_id) REFERENCES anomaly (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE anomaly ADD CONSTRAINT FK_F9F9756399295809 FOREIGN KEY (item_analysis_id) REFERENCES item_analysis (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE anomaly ADD CONSTRAINT FK_F9F975631613107C FOREIGN KEY (import_report_id) REFERENCES import_report (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14BAF977BB FOREIGN KEY (anomaly_id) REFERENCES anomaly (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice_tax_delivery_point_invoice ADD CONSTRAINT FK_CA91E35999EB98B FOREIGN KEY (invoice_tax_id) REFERENCES invoice_tax (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice_tax_delivery_point_invoice ADD CONSTRAINT FK_CA91E359B0954802 FOREIGN KEY (delivery_point_invoice_id) REFERENCES delivery_point_invoice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_pricing ADD CONSTRAINT FK_EDA0D1FB2576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_pricing ADD CONSTRAINT FK_EDA0D1FB8864AF73 FOREIGN KEY (pricing_id) REFERENCES pricing (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contract_pricing DROP FOREIGN KEY FK_EDA0D1FB2576E0FD');
        $this->addSql('ALTER TABLE delivery_point DROP FOREIGN KEY FK_A7AE15B62576E0FD');
        $this->addSql('ALTER TABLE delivery_point_invoice DROP FOREIGN KEY FK_2043A968A1492FCE');
        $this->addSql('ALTER TABLE invoice_tax_delivery_point_invoice DROP FOREIGN KEY FK_CA91E359B0954802');
        $this->addSql('ALTER TABLE delivery_point_invoice_analysis DROP FOREIGN KEY FK_E053799B0954802');
        $this->addSql('ALTER TABLE delivery_point_invoice_analysis DROP FOREIGN KEY FK_E053799B4C25520');
        $this->addSql('ALTER TABLE invoice_consumption DROP FOREIGN KEY FK_3BB14FCAB0954802');
        $this->addSql('ALTER TABLE invoice_subscription DROP FOREIGN KEY FK_1C014BA7B0954802');
        $this->addSql('ALTER TABLE delivery_point DROP FOREIGN KEY FK_A7AE15B61613107C');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517441613107C');
        $this->addSql('ALTER TABLE anomaly DROP FOREIGN KEY FK_F9F975631613107C');
        $this->addSql('ALTER TABLE delivery_point_invoice_analysis DROP FOREIGN KEY FK_E0537997941003F');
        $this->addSql('ALTER TABLE item_analysis DROP FOREIGN KEY FK_6C3E0FFB7941003F');
        $this->addSql('ALTER TABLE delivery_point_invoice DROP FOREIGN KEY FK_2043A9682989F1FD');
        $this->addSql('ALTER TABLE analysis DROP FOREIGN KEY FK_33C7302989F1FD');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14BAF977BB');
        $this->addSql('ALTER TABLE item_analysis DROP FOREIGN KEY FK_6C3E0FFBBAF977BB');
        $this->addSql('ALTER TABLE contract_pricing DROP FOREIGN KEY FK_EDA0D1FB8864AF73');
        $this->addSql('ALTER TABLE import_report DROP FOREIGN KEY FK_B81ECD9CA76ED395');
        $this->addSql('ALTER TABLE import DROP FOREIGN KEY FK_9D4ECE1DA76ED395');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F3610A76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14A76ED395');
        $this->addSql('ALTER TABLE import_report DROP FOREIGN KEY FK_B81ECD9CB6A263D9');
        $this->addSql('ALTER TABLE invoice_tax_delivery_point_invoice DROP FOREIGN KEY FK_CA91E35999EB98B');
        $this->addSql('ALTER TABLE delivery_point DROP FOREIGN KEY FK_A7AE15B67E9E4C8C');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744511FC912');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64986383B10');
        $this->addSql('ALTER TABLE import DROP FOREIGN KEY FK_9D4ECE1D93CB796C');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455F98F144A');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F285919EB6921');
        $this->addSql('ALTER TABLE delivery_point DROP FOREIGN KEY FK_A7AE15B619EB6921');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_9065174419EB6921');
        $this->addSql('ALTER TABLE pricing DROP FOREIGN KEY FK_E5F1AC3319EB6921');
        $this->addSql('ALTER TABLE tax DROP FOREIGN KEY FK_8E81BA7619EB6921');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64919EB6921');
        $this->addSql('ALTER TABLE item_analysis DROP FOREIGN KEY FK_6C3E0FFB74EE12FA');
        $this->addSql('ALTER TABLE anomaly DROP FOREIGN KEY FK_F9F9756399295809');
        $this->addSql('DROP TABLE contract');
        $this->addSql('DROP TABLE contract_pricing');
        $this->addSql('DROP TABLE delivery_point');
        $this->addSql('DROP TABLE delivery_point_invoice');
        $this->addSql('DROP TABLE import_report');
        $this->addSql('DROP TABLE analysis');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE anomaly');
        $this->addSql('DROP TABLE pricing');
        $this->addSql('DROP TABLE tax');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE import');
        $this->addSql('DROP TABLE invoice_tax');
        $this->addSql('DROP TABLE invoice_tax_delivery_point_invoice');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE delivery_point_invoice_analysis');
        $this->addSql('DROP TABLE item_analysis');
        $this->addSql('DROP TABLE invoice_consumption');
        $this->addSql('DROP TABLE invoice_subscription');
        $this->addSql('DROP TABLE refresh_tokens');
    }
}