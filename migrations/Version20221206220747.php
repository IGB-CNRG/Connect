<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221206220747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176E3542629');
        $this->addSql('ALTER TABLE person_entry_workflow_progress_person DROP FOREIGN KEY FK_E13DD1A2E3542629');
        $this->addSql('ALTER TABLE person_entry_workflow_progress_person DROP FOREIGN KEY FK_E13DD1A2217BBB47');
        $this->addSql('ALTER TABLE workflow_connection_member_category DROP FOREIGN KEY FK_A71C059F9B1ACD2F');
        $this->addSql('ALTER TABLE workflow_connection_member_category DROP FOREIGN KEY FK_A71C059FCB7B8920');
        $this->addSql('ALTER TABLE person_entry_workflow_progress DROP FOREIGN KEY FK_53090C76217BBB47');
        $this->addSql('DROP TABLE workflow_connection');
        $this->addSql('DROP TABLE person_entry_workflow_progress_person');
        $this->addSql('DROP TABLE workflow_connection_member_category');
        $this->addSql('DROP TABLE person_entry_workflow_progress');
        $this->addSql('DROP INDEX UNIQ_34DCD176E3542629 ON person');
        $this->addSql('ALTER TABLE person ADD membership_status VARCHAR(255) NOT NULL, DROP person_entry_workflow_progress_id');
        $this->addSql('ALTER TABLE workflow_notification ADD workflow_name VARCHAR(255) NOT NULL, ADD stage_name VARCHAR(255) NOT NULL, CHANGE person_entry_stage event VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE workflow_connection (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, url_template VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, person_entry_stage VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE person_entry_workflow_progress_person (person_entry_workflow_progress_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_E13DD1A2217BBB47 (person_id), INDEX IDX_E13DD1A2E3542629 (person_entry_workflow_progress_id), PRIMARY KEY(person_entry_workflow_progress_id, person_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE workflow_connection_member_category (workflow_connection_id INT NOT NULL, member_category_id INT NOT NULL, INDEX IDX_A71C059FCB7B8920 (member_category_id), INDEX IDX_A71C059F9B1ACD2F (workflow_connection_id), PRIMARY KEY(workflow_connection_id, member_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE person_entry_workflow_progress (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, stage VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_53090C76217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE person_entry_workflow_progress_person ADD CONSTRAINT FK_E13DD1A2E3542629 FOREIGN KEY (person_entry_workflow_progress_id) REFERENCES person_entry_workflow_progress (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person_entry_workflow_progress_person ADD CONSTRAINT FK_E13DD1A2217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workflow_connection_member_category ADD CONSTRAINT FK_A71C059F9B1ACD2F FOREIGN KEY (workflow_connection_id) REFERENCES workflow_connection (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workflow_connection_member_category ADD CONSTRAINT FK_A71C059FCB7B8920 FOREIGN KEY (member_category_id) REFERENCES member_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person_entry_workflow_progress ADD CONSTRAINT FK_53090C76217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE workflow_notification ADD person_entry_stage VARCHAR(255) NOT NULL, DROP event, DROP workflow_name, DROP stage_name');
        $this->addSql('ALTER TABLE person ADD person_entry_workflow_progress_id INT DEFAULT NULL, DROP membership_status');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176E3542629 FOREIGN KEY (person_entry_workflow_progress_id) REFERENCES person_entry_workflow_progress (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34DCD176E3542629 ON person (person_entry_workflow_progress_id)');
    }
}
