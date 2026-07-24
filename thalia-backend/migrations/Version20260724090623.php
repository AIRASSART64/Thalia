<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260724090623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE performance ADD season_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE performance ADD CONSTRAINT FK_82D796814EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('CREATE INDEX IDX_82D796814EC001D1 ON performance (season_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE performance DROP FOREIGN KEY FK_82D796814EC001D1');
        $this->addSql('DROP INDEX IDX_82D796814EC001D1 ON performance');
        $this->addSql('ALTER TABLE performance DROP season_id');
    }
}
