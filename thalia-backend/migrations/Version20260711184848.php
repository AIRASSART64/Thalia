<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260711184848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `show` (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, discipline VARCHAR(255) DEFAULT NULL, duration_min INT DEFAULT NULL, synopsis LONGTEXT DEFAULT NULL, min_stage_width DOUBLE PRECISION DEFAULT NULL, min_stage_depth DOUBLE PRECISION DEFAULT NULL, min_stage_height DOUBLE PRECISION DEFAULT NULL, pipeline_status VARCHAR(100) DEFAULT NULL, artwork_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, organization_id INT NOT NULL, INDEX IDX_320ED90132C8A3DE (organization_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE show_contact (show_id INT NOT NULL, contact_id INT NOT NULL, INDEX IDX_2B30CBDBD0C1FC64 (show_id), INDEX IDX_2B30CBDBE7A1254A (contact_id), PRIMARY KEY (show_id, contact_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE `show` ADD CONSTRAINT FK_320ED90132C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE show_contact ADD CONSTRAINT FK_2B30CBDBD0C1FC64 FOREIGN KEY (show_id) REFERENCES `show` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE show_contact ADD CONSTRAINT FK_2B30CBDBE7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `show` DROP FOREIGN KEY FK_320ED90132C8A3DE');
        $this->addSql('ALTER TABLE show_contact DROP FOREIGN KEY FK_2B30CBDBD0C1FC64');
        $this->addSql('ALTER TABLE show_contact DROP FOREIGN KEY FK_2B30CBDBE7A1254A');
        $this->addSql('DROP TABLE `show`');
        $this->addSql('DROP TABLE show_contact');
    }
}
