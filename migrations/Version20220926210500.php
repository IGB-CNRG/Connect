<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220926210500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C52C7C2CBA');
        $this->addSql('ALTER TABLE workflow_progress DROP FOREIGN KEY FK_E9620D5C71FE882C');
        $this->addSql('ALTER TABLE workflow_step_category DROP FOREIGN KEY FK_A083E98071FE882C');
        $this->addSql('CREATE TABLE workflow_connection (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url_template VARCHAR(255) NOT NULL, person_entry_stage LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workflow_connection_member_category (workflow_connection_id INT NOT NULL, member_category_id INT NOT NULL, INDEX IDX_A71C059F9B1ACD2F (workflow_connection_id), INDEX IDX_A71C059FCB7B8920 (member_category_id), PRIMARY KEY(workflow_connection_id, member_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workflow_notification (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, template LONGTEXT NOT NULL, recipients VARCHAR(255) NOT NULL, person_entry_stage LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workflow_notification_member_category (workflow_notification_id INT NOT NULL, member_category_id INT NOT NULL, INDEX IDX_D986E0B7B7AE9152 (workflow_notification_id), INDEX IDX_D986E0B7CB7B8920 (member_category_id), PRIMARY KEY(workflow_notification_id, member_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE workflow_connection_member_category ADD CONSTRAINT FK_A71C059F9B1ACD2F FOREIGN KEY (workflow_connection_id) REFERENCES workflow_connection (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workflow_connection_member_category ADD CONSTRAINT FK_A71C059FCB7B8920 FOREIGN KEY (member_category_id) REFERENCES member_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workflow_notification_member_category ADD CONSTRAINT FK_D986E0B7B7AE9152 FOREIGN KEY (workflow_notification_id) REFERENCES workflow_notification (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workflow_notification_member_category ADD CONSTRAINT FK_D986E0B7CB7B8920 FOREIGN KEY (member_category_id) REFERENCES member_category (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE workflow');
        $this->addSql('DROP TABLE workflow_progress');
        $this->addSql('DROP TABLE workflow_step');
        $this->addSql('DROP TABLE workflow_step_category');
        $this->addSql('DROP INDEX IDX_8F3F68C52C7C2CBA ON log');
        $this->addSql('ALTER TABLE log DROP workflow_id');
        $this->addSql('ALTER TABLE person ADD entry_stage VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE workflow_connection_member_category DROP FOREIGN KEY FK_A71C059F9B1ACD2F');
        $this->addSql('ALTER TABLE workflow_notification_member_category DROP FOREIGN KEY FK_D986E0B7B7AE9152');
        $this->addSql('CREATE TABLE workflow (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE workflow_progress (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, workflow_step_id INT NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_E9620D5C217BBB47 (person_id), INDEX IDX_E9620D5C71FE882C (workflow_step_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE workflow_step (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE workflow_step_category (id INT AUTO_INCREMENT NOT NULL, workflow_step_id INT NOT NULL, member_category_id INT NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_A083E98071FE882C (workflow_step_id), INDEX IDX_A083E980CB7B8920 (member_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE workflow_progress ADD CONSTRAINT FK_E9620D5C217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE workflow_progress ADD CONSTRAINT FK_E9620D5C71FE882C FOREIGN KEY (workflow_step_id) REFERENCES workflow_step (id)');
        $this->addSql('ALTER TABLE workflow_step_category ADD CONSTRAINT FK_A083E98071FE882C FOREIGN KEY (workflow_step_id) REFERENCES workflow_step (id)');
        $this->addSql('ALTER TABLE workflow_step_category ADD CONSTRAINT FK_A083E980CB7B8920 FOREIGN KEY (member_category_id) REFERENCES member_category (id)');
        $this->addSql('DROP TABLE workflow_connection');
        $this->addSql('DROP TABLE workflow_connection_member_category');
        $this->addSql('DROP TABLE workflow_notification');
        $this->addSql('DROP TABLE workflow_notification_member_category');
        $this->addSql('ALTER TABLE log ADD workflow_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C52C7C2CBA FOREIGN KEY (workflow_id) REFERENCES workflow (id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C52C7C2CBA ON log (workflow_id)');
        $this->addSql('ALTER TABLE person DROP entry_stage');
    }
}
