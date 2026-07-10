<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260706190746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organization ADD business_name VARCHAR(500) DEFAULT NULL, ADD vat_number VARCHAR(255) DEFAULT NULL, ADD safety_capacity INT DEFAULT NULL, ADD phone VARCHAR(50) DEFAULT NULL, ADD email VARCHAR(255) DEFAULT NULL, ADD erp_category LONGTEXT DEFAULT NULL, ADD erp_type VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organization DROP business_name, DROP vat_number, DROP safety_capacity, DROP phone, DROP email, DROP erp_category, DROP erp_type');
    }
}
