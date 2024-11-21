<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241121074800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player_info ADD schuhgroesse VARCHAR(31) NOT NULL');
        $this->addSql('ALTER TABLE team_info ADD shoe_size_xs INT NOT NULL, ADD shoe_size_s INT NOT NULL, ADD shoe_size_m INT NOT NULL, ADD shoe_size_l INT NOT NULL, ADD shoe_size_xl INT NOT NULL, ADD shoe_size_xxl INT NOT NULL, ADD shoe_size3_xl INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player_info DROP schuhgroesse');
        $this->addSql('ALTER TABLE team_info DROP shoe_size_xs, DROP shoe_size_s, DROP shoe_size_m, DROP shoe_size_l, DROP shoe_size_xl, DROP shoe_size_xxl, DROP shoe_size3_xl');
    }
}
