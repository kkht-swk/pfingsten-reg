<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240411202114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bank_account (id INT AUTO_INCREMENT NOT NULL, iban VARCHAR(63) NOT NULL, bic VARCHAR(63) NOT NULL, bank VARCHAR(63) NOT NULL, kontoinhaber VARCHAR(63) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kontakt (id INT AUTO_INCREMENT NOT NULL, vorname VARCHAR(63) NOT NULL, nachname VARCHAR(63) NOT NULL, email VARCHAR(63) NOT NULL, phone VARCHAR(63) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player_info (id INT AUTO_INCREMENT NOT NULL, kontakt_id INT NOT NULL, account_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', hashkey VARCHAR(255) NOT NULL, vorname VARCHAR(63) NOT NULL, nachname VARCHAR(63) NOT NULL, altersklasse VARCHAR(31) NOT NULL, nahrung VARCHAR(31) NOT NULL, UNIQUE INDEX UNIQ_69936164C4062E7F (kontakt_id), UNIQUE INDEX UNIQ_699361649B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team_info (id INT AUTO_INCREMENT NOT NULL, kontakt_id INT NOT NULL, account_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', hashkey VARCHAR(255) NOT NULL, last_saved_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', verein VARCHAR(63) NOT NULL, altersklasse VARCHAR(31) NOT NULL, ankunftszeit VARCHAR(63) NOT NULL, name VARCHAR(63) DEFAULT NULL, logo_path VARCHAR(255) DEFAULT NULL, picture_path VARCHAR(255) DEFAULT NULL, spieler_vegan INT NOT NULL, spieler_fleisch INT NOT NULL, betreuer_vegan INT NOT NULL, betreuer_fleisch INT NOT NULL, gaeste INT NOT NULL, bemerkung VARCHAR(1023) DEFAULT NULL, UNIQUE INDEX UNIQ_1A68C52CC4062E7F (kontakt_id), UNIQUE INDEX UNIQ_1A68C52C9B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE player_info ADD CONSTRAINT FK_69936164C4062E7F FOREIGN KEY (kontakt_id) REFERENCES kontakt (id)');
        $this->addSql('ALTER TABLE player_info ADD CONSTRAINT FK_699361649B6B5FBA FOREIGN KEY (account_id) REFERENCES bank_account (id)');
        $this->addSql('ALTER TABLE team_info ADD CONSTRAINT FK_1A68C52CC4062E7F FOREIGN KEY (kontakt_id) REFERENCES kontakt (id)');
        $this->addSql('ALTER TABLE team_info ADD CONSTRAINT FK_1A68C52C9B6B5FBA FOREIGN KEY (account_id) REFERENCES bank_account (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player_info DROP FOREIGN KEY FK_69936164C4062E7F');
        $this->addSql('ALTER TABLE player_info DROP FOREIGN KEY FK_699361649B6B5FBA');
        $this->addSql('ALTER TABLE team_info DROP FOREIGN KEY FK_1A68C52CC4062E7F');
        $this->addSql('ALTER TABLE team_info DROP FOREIGN KEY FK_1A68C52C9B6B5FBA');
        $this->addSql('DROP TABLE bank_account');
        $this->addSql('DROP TABLE kontakt');
        $this->addSql('DROP TABLE player_info');
        $this->addSql('DROP TABLE team_info');
    }
}
