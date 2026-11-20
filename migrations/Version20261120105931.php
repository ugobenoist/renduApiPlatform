<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261120105931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pays_langue (pays_id INTEGER NOT NULL, langue_id INTEGER NOT NULL, PRIMARY KEY (pays_id, langue_id), CONSTRAINT FK_225722DFA6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_225722DF2AADBACD FOREIGN KEY (langue_id) REFERENCES langue (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_225722DFA6E44244 ON pays_langue (pays_id)');
        $this->addSql('CREATE INDEX IDX_225722DF2AADBACD ON pays_langue (langue_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__continent AS SELECT id, nom FROM continent');
        $this->addSql('DROP TABLE continent');
        $this->addSql('CREATE TABLE continent (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL)');
        $this->addSql('INSERT INTO continent (id, nom) SELECT id, nom FROM __temp__continent');
        $this->addSql('DROP TABLE __temp__continent');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6CC70C7C6C6E55B5 ON continent (nom)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__langue AS SELECT id, nom, code_iso FROM langue');
        $this->addSql('DROP TABLE langue');
        $this->addSql('CREATE TABLE langue (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, code_iso VARCHAR(3) NOT NULL)');
        $this->addSql('INSERT INTO langue (id, nom, code_iso) SELECT id, nom, code_iso FROM __temp__langue');
        $this->addSql('DROP TABLE __temp__langue');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9357758E6C6E55B5 ON langue (nom)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9357758E897848EB ON langue (code_iso)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__pays AS SELECT id, nom, code, population, superficie, capitale FROM pays');
        $this->addSql('DROP TABLE pays');
        $this->addSql('CREATE TABLE pays (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, code VARCHAR(3) NOT NULL, population INTEGER DEFAULT NULL, superficie INTEGER NOT NULL, capitale VARCHAR(150) NOT NULL, continent_id INTEGER NOT NULL, CONSTRAINT FK_349F3CAE921F4C77 FOREIGN KEY (continent_id) REFERENCES continent (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO pays (id, nom, code, population, superficie, capitale) SELECT id, nom, code, population, superficie, capitale FROM __temp__pays');
        $this->addSql('DROP TABLE __temp__pays');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_349F3CAE6C6E55B5 ON pays (nom)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_349F3CAE77153098 ON pays (code)');
        $this->addSql('CREATE INDEX IDX_349F3CAE921F4C77 ON pays (continent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE pays_langue');
        $this->addSql('CREATE TEMPORARY TABLE __temp__continent AS SELECT id, nom FROM continent');
        $this->addSql('DROP TABLE continent');
        $this->addSql('CREATE TABLE continent (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL)');
        $this->addSql('INSERT INTO continent (id, nom) SELECT id, nom FROM __temp__continent');
        $this->addSql('DROP TABLE __temp__continent');
        $this->addSql('CREATE TEMPORARY TABLE __temp__langue AS SELECT id, nom, code_iso FROM langue');
        $this->addSql('DROP TABLE langue');
        $this->addSql('CREATE TABLE langue (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, code_iso VARCHAR(3) NOT NULL)');
        $this->addSql('INSERT INTO langue (id, nom, code_iso) SELECT id, nom, code_iso FROM __temp__langue');
        $this->addSql('DROP TABLE __temp__langue');
        $this->addSql('CREATE TEMPORARY TABLE __temp__pays AS SELECT id, nom, code, population, superficie, capitale FROM pays');
        $this->addSql('DROP TABLE pays');
        $this->addSql('CREATE TABLE pays (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, code VARCHAR(3) NOT NULL, population INTEGER DEFAULT NULL, superficie INTEGER NOT NULL, capitale VARCHAR(150) NOT NULL)');
        $this->addSql('INSERT INTO pays (id, nom, code, population, superficie, capitale) SELECT id, nom, code, population, superficie, capitale FROM __temp__pays');
        $this->addSql('DROP TABLE __temp__pays');
    }
}
