<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260711094302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
       {
        // 1. On ajoute d'abord les colonnes en autorisant temporairement la valeur NULL (ou avec une valeur par défaut)
        $this->addSql('ALTER TABLE organization ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'(DC2Type:datetime_immutable)\'');
        
        // 2. (Optionnel mais propre) On supprime la valeur par défaut pour les futurs enregistrements (c'est Doctrine qui s'en chargera via le Lifecycle Callback)
        $this->addSql('ALTER TABLE organization ALTER created_at DROP DEFAULT, ALTER updated_at DROP DEFAULT');
    }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organization DROP created_at, DROP updated_at');
    }
}
