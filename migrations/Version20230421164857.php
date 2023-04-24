<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230421164857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exit_form ADD forwarding_email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE member_category ADD can_supervise TINYINT(1) NOT NULL, ADD needs_certificates TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE member_category DROP can_supervise, DROP needs_certificates');
        $this->addSql('ALTER TABLE exit_form DROP forwarding_email');
    }
}
