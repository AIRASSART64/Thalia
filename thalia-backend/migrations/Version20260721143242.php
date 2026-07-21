<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721143242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE financial ADD organization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE financial ADD CONSTRAINT FK_6E80AAAC32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('CREATE INDEX IDX_6E80AAAC32C8A3DE ON financial (organization_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE financial DROP FOREIGN KEY FK_6E80AAAC32C8A3DE');
        $this->addSql('DROP INDEX IDX_6E80AAAC32C8A3DE ON financial');
        $this->addSql('ALTER TABLE financial DROP organization_id');
    }
}
