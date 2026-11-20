<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261120100325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE continent (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL)');
        $this->addSql('CREATE TABLE langue (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, code_iso VARCHAR(3) NOT NULL)');
        $this->addSql('CREATE TABLE pays (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, code VARCHAR(3) NOT NULL, population INTEGER DEFAULT NULL, superficie INTEGER NOT NULL, capitale VARCHAR(150) NOT NULL)');
        $this->addSql('CREATE TABLE pays_pays (pays_source INTEGER NOT NULL, pays_target INTEGER NOT NULL, PRIMARY KEY (pays_source, pays_target), CONSTRAINT FK_51352661EE8A2822 FOREIGN KEY (pays_source) REFERENCES pays (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_51352661F76F78AD FOREIGN KEY (pays_target) REFERENCES pays (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_51352661EE8A2822 ON pays_pays (pays_source)');
        $this->addSql('CREATE INDEX IDX_51352661F76F78AD ON pays_pays (pays_target)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE continent');
        $this->addSql('DROP TABLE langue');
        $this->addSql('DROP TABLE pays');
        $this->addSql('DROP TABLE pays_pays');
    }
}
