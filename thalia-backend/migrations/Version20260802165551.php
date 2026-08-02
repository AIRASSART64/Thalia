<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260802165551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE show_contact DROP FOREIGN KEY `FK_2B30CBDBD0C1FC64`');
        $this->addSql('DROP INDEX IDX_2B30CBDBD0C1FC64 ON show_contact');
        $this->addSql('ALTER TABLE show_contact ADD id INT AUTO_INCREMENT NOT NULL, ADD report LONGTEXT DEFAULT NULL, CHANGE show_id event_id INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE show_contact ADD CONSTRAINT FK_2B30CBDB71F7E88B FOREIGN KEY (event_id) REFERENCES `show` (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_2B30CBDB71F7E88B ON show_contact (event_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE show_contact DROP FOREIGN KEY FK_2B30CBDB71F7E88B');
        $this->addSql('DROP INDEX IDX_2B30CBDB71F7E88B ON show_contact');
        $this->addSql('ALTER TABLE show_contact MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE show_contact DROP id, DROP report, CHANGE event_id show_id INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (show_id, contact_id)');
        $this->addSql('ALTER TABLE show_contact ADD CONSTRAINT `FK_2B30CBDBD0C1FC64` FOREIGN KEY (show_id) REFERENCES `show` (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_2B30CBDBD0C1FC64 ON show_contact (show_id)');
    }
}
