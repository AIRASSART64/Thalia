<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260717133519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipment (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, category LONGTEXT DEFAULT NULL, total_quantity INT DEFAULT NULL, venue_id INT DEFAULT NULL, INDEX IDX_D338D58340A73EBA (venue_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE venue (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, max_capacity INT DEFAULT NULL, seats_count INT DEFAULT NULL, standing_count INT DEFAULT NULL, pmr_count INT DEFAULT NULL, invitation_quota INT DEFAULT NULL, stage_width DOUBLE PRECISION DEFAULT NULL, stage_depth DOUBLE PRECISION DEFAULT NULL, stage_height DOUBLE PRECISION DEFAULT NULL, organization_id INT DEFAULT NULL, INDEX IDX_91911B0D32C8A3DE (organization_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D58340A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE venue ADD CONSTRAINT FK_91911B0D32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_D338D58340A73EBA');
        $this->addSql('ALTER TABLE venue DROP FOREIGN KEY FK_91911B0D32C8A3DE');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE venue');
    }
}
