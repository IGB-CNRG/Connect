<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230602195224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person ADD last_reviewed_by_id INT DEFAULT NULL, ADD last_reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176926C2875 FOREIGN KEY (last_reviewed_by_id) REFERENCES person (id)');
        $this->addSql('CREATE INDEX IDX_34DCD176926C2875 ON person (last_reviewed_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176926C2875');
        $this->addSql('DROP INDEX IDX_34DCD176926C2875 ON person');
        $this->addSql('ALTER TABLE person DROP last_reviewed_by_id, DROP last_reviewed_at');
    }
}
