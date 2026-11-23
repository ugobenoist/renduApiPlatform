<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261123222427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remplace la relation ManyToOne Pays-Continent par une relation ManyToMany.';
    }

    public function up(Schema $schema): void
    {
        // 1. Création de la table intermédiaire
        $this->addSql('
            CREATE TABLE pays_continent (
                pays_id INTEGER NOT NULL,
                continent_id INTEGER NOT NULL,
                PRIMARY KEY(pays_id, continent_id),
                CONSTRAINT FK_PAYS_CONTINENT_PAYS
                    FOREIGN KEY (pays_id) REFERENCES pays (id) ON DELETE CASCADE,
                CONSTRAINT FK_PAYS_CONTINENT_CONTINENT
                    FOREIGN KEY (continent_id) REFERENCES continent (id) ON DELETE CASCADE
            )
        ');

        // 2. Transfert des associations existantes
        $this->addSql('
            INSERT INTO pays_continent (pays_id, continent_id)
            SELECT id, continent_id
            FROM pays
            WHERE continent_id IS NOT NULL
        ');

        // 3. Suppression de l'ancienne colonne continent_id
        // SQLite nécessite de reconstruire la table.
        $this->addSql('
            CREATE TABLE pays_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                nom VARCHAR(255) NOT NULL,
                code VARCHAR(3) NOT NULL,
                population INTEGER DEFAULT NULL,
                superficie INTEGER DEFAULT NULL,
                capitale VARCHAR(150) DEFAULT NULL
            )
        ');

        $this->addSql('
            INSERT INTO pays_new (id, nom, code, population, superficie, capitale)
            SELECT id, nom, code, population, superficie, capitale
            FROM pays
        ');

        $this->addSql('DROP TABLE pays');
        $this->addSql('ALTER TABLE pays_new RENAME TO pays');

        // 4. Index pour les relations
        $this->addSql('
            CREATE INDEX IDX_PAYS_CONTINENT_PAYS
            ON pays_continent (pays_id)
        ');

        $this->addSql('
            CREATE INDEX IDX_PAYS_CONTINENT_CONTINENT
            ON pays_continent (continent_id)
        ');
    }

    public function down(Schema $schema): void
    {
        // Restauration de l'ancienne structure
        $this->addSql('
            CREATE TABLE pays_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                nom VARCHAR(255) NOT NULL,
                code VARCHAR(3) NOT NULL,
                population INTEGER DEFAULT NULL,
                superficie INTEGER DEFAULT NULL,
                capitale VARCHAR(150) DEFAULT NULL,
                continent_id INTEGER NOT NULL,
                CONSTRAINT FK_PAYS_CONTINENT
                    FOREIGN KEY (continent_id) REFERENCES continent (id)
            )
        ');

        $this->addSql('
            INSERT INTO pays_old (id, nom, code, population, superficie, capitale, continent_id)
            SELECT p.id, p.nom, p.code, p.population, p.superficie, p.capitale,
                   pc.continent_id
            FROM pays p
            LEFT JOIN pays_continent pc ON pc.pays_id = p.id
        ');

        $this->addSql('DROP TABLE pays_continent');
        $this->addSql('DROP TABLE pays');
        $this->addSql('ALTER TABLE pays_old RENAME TO pays');

        $this->addSql('
            CREATE INDEX IDX_PAYS_CONTINENT_ID
            ON pays (continent_id)
        ');
    }
}
