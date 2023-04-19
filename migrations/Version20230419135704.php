<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230419135704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE building (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, short_name VARCHAR(255) DEFAULT NULL, address LONGTEXT DEFAULT NULL, building_number INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, uploaded_by_id INT NOT NULL, file_name VARCHAR(255) DEFAULT NULL, mime_type VARCHAR(255) DEFAULT NULL, original_name VARCHAR(255) DEFAULT NULL, display_name VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_D8698A76217BBB47 (person_id), INDEX IDX_D8698A76A2B28FE8 (uploaded_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exit_form (id INT AUTO_INCREMENT NOT NULL, ended_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', exit_reason VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `key` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE key_room (key_id INT NOT NULL, room_id INT NOT NULL, INDEX IDX_F4988A52D145533 (key_id), INDEX IDX_F4988A5254177093 (room_id), PRIMARY KEY(key_id, room_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE key_affiliation (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, cylinder_key_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, INDEX IDX_B11C1673217BBB47 (person_id), INDEX IDX_B11C1673AA7F73B8 (cylinder_key_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE log (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, person_id INT DEFAULT NULL, theme_id INT DEFAULT NULL, room_id INT DEFAULT NULL, cylinder_key_id INT DEFAULT NULL, unit_id INT DEFAULT NULL, text VARCHAR(255) NOT NULL, context LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_8F3F68C5A76ED395 (user_id), INDEX IDX_8F3F68C5217BBB47 (person_id), INDEX IDX_8F3F68C559027487 (theme_id), INDEX IDX_8F3F68C554177093 (room_id), INDEX IDX_8F3F68C5AA7F73B8 (cylinder_key_id), INDEX IDX_8F3F68C5F8BD700D (unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, short_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, office_building_id INT DEFAULT NULL, exit_form_id INT DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, middle_initial VARCHAR(255) DEFAULT NULL, netid VARCHAR(255) DEFAULT NULL, username VARCHAR(180) DEFAULT NULL, uin INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, office_number VARCHAR(255) DEFAULT NULL, office_phone VARCHAR(255) DEFAULT NULL, has_given_key_deposit TINYINT(1) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', preferred_first_name VARCHAR(255) DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, mime_type VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, slug VARCHAR(255) DEFAULT NULL, other_address LONGTEXT DEFAULT NULL, membership_status VARCHAR(255) NOT NULL, office_work_only TINYINT(1) DEFAULT NULL, membership_updated_at DATETIME NOT NULL, membership_note VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_34DCD176F85E0677 (username), UNIQUE INDEX UNIQ_34DCD176989D9B62 (slug), INDEX IDX_34DCD17677C03CA0 (office_building_id), UNIQUE INDEX UNIQ_34DCD17641E476CB (exit_form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_729F519B5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room_affiliation (id INT AUTO_INCREMENT NOT NULL, room_id INT NOT NULL, person_id INT NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_FE47BB0F54177093 (room_id), INDEX IDX_FE47BB0F217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE setting (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL, display_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supervisor_affiliation (id INT AUTO_INCREMENT NOT NULL, supervisor_id INT NOT NULL, supervisee_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, INDEX IDX_7681703619E9AC5F (supervisor_id), INDEX IDX_768170369E97DBD8 (supervisee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, short_name VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, is_non_research TINYINT(1) NOT NULL, is_outside_group TINYINT(1) NOT NULL, admin_email VARCHAR(255) DEFAULT NULL, lab_manager_email VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE theme_affiliation (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, theme_id INT NOT NULL, member_category_id INT NOT NULL, title VARCHAR(255) DEFAULT NULL, theme_roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', exit_reason VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, INDEX IDX_259063F217BBB47 (person_id), INDEX IDX_259063F59027487 (theme_id), INDEX IDX_259063FCB7B8920 (member_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE unit (id INT AUTO_INCREMENT NOT NULL, parent_unit_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, short_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_DCBB0C538AF5044B (parent_unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE unit_affiliation (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, unit_id INT DEFAULT NULL, other_unit VARCHAR(255) DEFAULT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_71157F16217BBB47 (person_id), INDEX IDX_71157F16F8BD700D (unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workflow_notification (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, template LONGTEXT NOT NULL, recipients VARCHAR(255) NOT NULL, workflow_name VARCHAR(255) NOT NULL, transition_name VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, is_enabled TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workflow_notification_member_category (workflow_notification_id INT NOT NULL, member_category_id INT NOT NULL, INDEX IDX_D986E0B7B7AE9152 (workflow_notification_id), INDEX IDX_D986E0B7CB7B8920 (member_category_id), PRIMARY KEY(workflow_notification_id, member_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76A2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE key_room ADD CONSTRAINT FK_F4988A52D145533 FOREIGN KEY (key_id) REFERENCES `key` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE key_room ADD CONSTRAINT FK_F4988A5254177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE key_affiliation ADD CONSTRAINT FK_B11C1673217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE key_affiliation ADD CONSTRAINT FK_B11C1673AA7F73B8 FOREIGN KEY (cylinder_key_id) REFERENCES `key` (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C559027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C554177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5AA7F73B8 FOREIGN KEY (cylinder_key_id) REFERENCES `key` (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD17677C03CA0 FOREIGN KEY (office_building_id) REFERENCES building (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD17641E476CB FOREIGN KEY (exit_form_id) REFERENCES exit_form (id)');
        $this->addSql('ALTER TABLE room_affiliation ADD CONSTRAINT FK_FE47BB0F54177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE room_affiliation ADD CONSTRAINT FK_FE47BB0F217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE supervisor_affiliation ADD CONSTRAINT FK_7681703619E9AC5F FOREIGN KEY (supervisor_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE supervisor_affiliation ADD CONSTRAINT FK_768170369E97DBD8 FOREIGN KEY (supervisee_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE theme_affiliation ADD CONSTRAINT FK_259063F217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE theme_affiliation ADD CONSTRAINT FK_259063F59027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE theme_affiliation ADD CONSTRAINT FK_259063FCB7B8920 FOREIGN KEY (member_category_id) REFERENCES member_category (id)');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C538AF5044B FOREIGN KEY (parent_unit_id) REFERENCES unit (id)');
        $this->addSql('ALTER TABLE unit_affiliation ADD CONSTRAINT FK_71157F16217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE unit_affiliation ADD CONSTRAINT FK_71157F16F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('ALTER TABLE workflow_notification_member_category ADD CONSTRAINT FK_D986E0B7B7AE9152 FOREIGN KEY (workflow_notification_id) REFERENCES workflow_notification (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workflow_notification_member_category ADD CONSTRAINT FK_D986E0B7CB7B8920 FOREIGN KEY (member_category_id) REFERENCES member_category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76217BBB47');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76A2B28FE8');
        $this->addSql('ALTER TABLE key_room DROP FOREIGN KEY FK_F4988A52D145533');
        $this->addSql('ALTER TABLE key_room DROP FOREIGN KEY FK_F4988A5254177093');
        $this->addSql('ALTER TABLE key_affiliation DROP FOREIGN KEY FK_B11C1673217BBB47');
        $this->addSql('ALTER TABLE key_affiliation DROP FOREIGN KEY FK_B11C1673AA7F73B8');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5A76ED395');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5217BBB47');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C559027487');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C554177093');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5AA7F73B8');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5F8BD700D');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD17677C03CA0');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD17641E476CB');
        $this->addSql('ALTER TABLE room_affiliation DROP FOREIGN KEY FK_FE47BB0F54177093');
        $this->addSql('ALTER TABLE room_affiliation DROP FOREIGN KEY FK_FE47BB0F217BBB47');
        $this->addSql('ALTER TABLE supervisor_affiliation DROP FOREIGN KEY FK_7681703619E9AC5F');
        $this->addSql('ALTER TABLE supervisor_affiliation DROP FOREIGN KEY FK_768170369E97DBD8');
        $this->addSql('ALTER TABLE theme_affiliation DROP FOREIGN KEY FK_259063F217BBB47');
        $this->addSql('ALTER TABLE theme_affiliation DROP FOREIGN KEY FK_259063F59027487');
        $this->addSql('ALTER TABLE theme_affiliation DROP FOREIGN KEY FK_259063FCB7B8920');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C538AF5044B');
        $this->addSql('ALTER TABLE unit_affiliation DROP FOREIGN KEY FK_71157F16217BBB47');
        $this->addSql('ALTER TABLE unit_affiliation DROP FOREIGN KEY FK_71157F16F8BD700D');
        $this->addSql('ALTER TABLE workflow_notification_member_category DROP FOREIGN KEY FK_D986E0B7B7AE9152');
        $this->addSql('ALTER TABLE workflow_notification_member_category DROP FOREIGN KEY FK_D986E0B7CB7B8920');
        $this->addSql('DROP TABLE building');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE exit_form');
        $this->addSql('DROP TABLE `key`');
        $this->addSql('DROP TABLE key_room');
        $this->addSql('DROP TABLE key_affiliation');
        $this->addSql('DROP TABLE log');
        $this->addSql('DROP TABLE member_category');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE room_affiliation');
        $this->addSql('DROP TABLE setting');
        $this->addSql('DROP TABLE supervisor_affiliation');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE theme_affiliation');
        $this->addSql('DROP TABLE unit');
        $this->addSql('DROP TABLE unit_affiliation');
        $this->addSql('DROP TABLE workflow_notification');
        $this->addSql('DROP TABLE workflow_notification_member_category');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
