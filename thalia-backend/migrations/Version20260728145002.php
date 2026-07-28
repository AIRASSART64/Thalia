<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260728145002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE show_theme (show_id INT NOT NULL, theme_id INT NOT NULL, INDEX IDX_95C483D4D0C1FC64 (show_id), INDEX IDX_95C483D459027487 (theme_id), PRIMARY KEY (show_id, theme_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, organization_id INT NOT NULL, INDEX IDX_9775E70832C8A3DE (organization_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE show_theme ADD CONSTRAINT FK_95C483D4D0C1FC64 FOREIGN KEY (show_id) REFERENCES `show` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE show_theme ADD CONSTRAINT FK_95C483D459027487 FOREIGN KEY (theme_id) REFERENCES theme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E70832C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE `show` DROP themes, CHANGE audience audience VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE show_theme DROP FOREIGN KEY FK_95C483D4D0C1FC64');
        $this->addSql('ALTER TABLE show_theme DROP FOREIGN KEY FK_95C483D459027487');
        $this->addSql('ALTER TABLE theme DROP FOREIGN KEY FK_9775E70832C8A3DE');
        $this->addSql('DROP TABLE show_theme');
        $this->addSql('DROP TABLE theme');
        $this->addSql('ALTER TABLE `show` ADD themes JSON DEFAULT NULL, CHANGE audience audience VARCHAR(255) NOT NULL');
    }
}
