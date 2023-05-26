<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230525202657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE theme ADD parent_theme_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E708C6CFD856 FOREIGN KEY (parent_theme_id) REFERENCES theme (id)');
        $this->addSql('CREATE INDEX IDX_9775E708C6CFD856 ON theme (parent_theme_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE theme DROP FOREIGN KEY FK_9775E708C6CFD856');
        $this->addSql('DROP INDEX IDX_9775E708C6CFD856 ON theme');
        $this->addSql('ALTER TABLE theme DROP parent_theme_id');
    }
}
