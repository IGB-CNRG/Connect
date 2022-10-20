<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221018195226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE person_entry_workflow_progress (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, stage VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_53090C76217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person_entry_workflow_progress_person (person_entry_workflow_progress_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_E13DD1A2E3542629 (person_entry_workflow_progress_id), INDEX IDX_E13DD1A2217BBB47 (person_id), PRIMARY KEY(person_entry_workflow_progress_id, person_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE person_entry_workflow_progress ADD CONSTRAINT FK_53090C76217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE person_entry_workflow_progress_person ADD CONSTRAINT FK_E13DD1A2E3542629 FOREIGN KEY (person_entry_workflow_progress_id) REFERENCES person_entry_workflow_progress (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person_entry_workflow_progress_person ADD CONSTRAINT FK_E13DD1A2217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person DROP entry_stage');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person_entry_workflow_progress DROP FOREIGN KEY FK_53090C76217BBB47');
        $this->addSql('ALTER TABLE person_entry_workflow_progress_person DROP FOREIGN KEY FK_E13DD1A2E3542629');
        $this->addSql('ALTER TABLE person_entry_workflow_progress_person DROP FOREIGN KEY FK_E13DD1A2217BBB47');
        $this->addSql('DROP TABLE person_entry_workflow_progress');
        $this->addSql('DROP TABLE person_entry_workflow_progress_person');
        $this->addSql('ALTER TABLE person ADD entry_stage VARCHAR(255) DEFAULT NULL');
    }
}
