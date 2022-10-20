<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221018202740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person ADD person_entry_workflow_progress_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176E3542629 FOREIGN KEY (person_entry_workflow_progress_id) REFERENCES person_entry_workflow_progress (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34DCD176E3542629 ON person (person_entry_workflow_progress_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176E3542629');
        $this->addSql('DROP INDEX UNIQ_34DCD176E3542629 ON person');
        $this->addSql('ALTER TABLE person DROP person_entry_workflow_progress_id');
    }
}
