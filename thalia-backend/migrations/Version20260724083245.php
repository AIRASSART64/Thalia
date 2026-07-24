<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260724083245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE performance (id INT AUTO_INCREMENT NOT NULL, date_time_start DATETIME NOT NULL, date_time_end DATETIME NOT NULL, setup_duration_min INT DEFAULT NULL, teardown_duration_min INT DEFAULT NULL, ticket_price_standard NUMERIC(6, 2) DEFAULT NULL, ticket_price_reduced NUMERIC(6, 2) DEFAULT NULL, estimated_attendance_percent INT DEFAULT NULL, total_cost NUMERIC(10, 2) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, organization_id INT NOT NULL, venue_id INT DEFAULT NULL, season_show_id INT DEFAULT NULL, INDEX IDX_82D7968132C8A3DE (organization_id), INDEX IDX_82D7968140A73EBA (venue_id), INDEX IDX_82D79681F5B8553A (season_show_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE performance ADD CONSTRAINT FK_82D7968132C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE performance ADD CONSTRAINT FK_82D7968140A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE performance ADD CONSTRAINT FK_82D79681F5B8553A FOREIGN KEY (season_show_id) REFERENCES `show` (id)');
        $this->addSql('ALTER TABLE financial CHANGE season_id season_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE performance DROP FOREIGN KEY FK_82D7968132C8A3DE');
        $this->addSql('ALTER TABLE performance DROP FOREIGN KEY FK_82D7968140A73EBA');
        $this->addSql('ALTER TABLE performance DROP FOREIGN KEY FK_82D79681F5B8553A');
        $this->addSql('DROP TABLE performance');
        $this->addSql('ALTER TABLE financial CHANGE season_id season_id INT DEFAULT NULL');
    }
}
