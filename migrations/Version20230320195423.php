<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230320195423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exit_form (id INT AUTO_INCREMENT NOT NULL, ended_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', exit_reason VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE person ADD exit_form_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD17641E476CB FOREIGN KEY (exit_form_id) REFERENCES exit_form (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34DCD17641E476CB ON person (exit_form_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD17641E476CB');
        $this->addSql('DROP TABLE exit_form');
        $this->addSql('DROP INDEX UNIQ_34DCD17641E476CB ON person');
        $this->addSql('ALTER TABLE person DROP exit_form_id');
    }
}
