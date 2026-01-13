<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260113170333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, customer_id VARCHAR(30) NOT NULL, customer_name VARCHAR(50) NOT NULL, country VARCHAR(100) NOT NULL, city VARCHAR(100) NOT NULL, postal_code VARCHAR(10) NOT NULL, UNIQUE INDEX UNIQ_81398E096B899279 (patient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hospital (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(67) NOT NULL, adresse VARCHAR(50) NOT NULL, ville VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE medecin (id INT AUTO_INCREMENT NOT NULL, specialite_id INT NOT NULL, hospital_id INT NOT NULL, license_number VARCHAR(10) NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, specialization VARCHAR(100) NOT NULL, phone VARCHAR(15) DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, date_of_birth DATE DEFAULT NULL, license_issue_date DATE NOT NULL, license_expiry_date DATE DEFAULT NULL, address VARCHAR(100) NOT NULL, city VARCHAR(50) NOT NULL, postal_code VARCHAR(10) NOT NULL, country VARCHAR(50) NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, biography LONGTEXT DEFAULT NULL, consultation_fee NUMERIC(5, 2) DEFAULT NULL, nationality VARCHAR(50) DEFAULT NULL, gender VARCHAR(20) DEFAULT NULL, UNIQUE INDEX UNIQ_1BDA53C6EC7E7152 (license_number), INDEX IDX_1BDA53C62195E0F0 (specialite_id), INDEX IDX_1BDA53C663DBB69 (hospital_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE patient (id INT AUTO_INCREMENT NOT NULL, patient_number VARCHAR(20) NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, date_of_birth DATE NOT NULL, gender VARCHAR(20) DEFAULT NULL, phone VARCHAR(15) DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, address VARCHAR(200) NOT NULL, city VARCHAR(50) NOT NULL, postal_code VARCHAR(10) NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, registration_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, UNIQUE INDEX UNIQ_1ADAD7EB9DDEF6AE (patient_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rendez_vous (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, medecin_id INT NOT NULL, reference VARCHAR(20) NOT NULL, date_heure DATETIME NOT NULL, type VARCHAR(50) NOT NULL, motif LONGTEXT DEFAULT NULL, statut VARCHAR(20) NOT NULL, duree INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, prix NUMERIC(8, 2) DEFAULT NULL, mode_paiement VARCHAR(20) DEFAULT NULL, paiement_effectue TINYINT(1) DEFAULT NULL, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, date_modification DATETIME DEFAULT NULL, date_annulation DATETIME DEFAULT NULL, raison_annulation LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_65E8AA0AAEA34913 (reference), INDEX IDX_65E8AA0A6B899279 (patient_id), INDEX IDX_65E8AA0A4F31A84 (medecin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE specialite (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, description VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_E7D6FCC16C6E55B5 (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E096B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE medecin ADD CONSTRAINT FK_1BDA53C62195E0F0 FOREIGN KEY (specialite_id) REFERENCES specialite (id)');
        $this->addSql('ALTER TABLE medecin ADD CONSTRAINT FK_1BDA53C663DBB69 FOREIGN KEY (hospital_id) REFERENCES hospital (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A4F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E096B899279');
        $this->addSql('ALTER TABLE medecin DROP FOREIGN KEY FK_1BDA53C62195E0F0');
        $this->addSql('ALTER TABLE medecin DROP FOREIGN KEY FK_1BDA53C663DBB69');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A6B899279');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A4F31A84');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE hospital');
        $this->addSql('DROP TABLE medecin');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE rendez_vous');
        $this->addSql('DROP TABLE specialite');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
