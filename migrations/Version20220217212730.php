<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220217212730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE building (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, short_name VARCHAR(255) NOT NULL, address LONGTEXT DEFAULT NULL, building_number INT DEFAULT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE college (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE department (id INT AUTO_INCREMENT NOT NULL, college_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_CD1DE18A770124B2 (college_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE department_affiliation (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, department_id INT NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, INDEX IDX_435CB26F217BBB47 (person_id), INDEX IDX_435CB26FAE80F5DF (department_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `key` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE key_affiliation (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, cylinder_key_id INT NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, INDEX IDX_B11C1673217BBB47 (person_id), INDEX IDX_B11C1673AA7F73B8 (cylinder_key_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, person_id INT DEFAULT NULL, theme_id INT DEFAULT NULL, room_id INT DEFAULT NULL, cylinder_key_id INT DEFAULT NULL, workflow_id INT DEFAULT NULL, text VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_8F3F68C5A76ED395 (user_id), INDEX IDX_8F3F68C5217BBB47 (person_id), INDEX IDX_8F3F68C559027487 (theme_id), INDEX IDX_8F3F68C554177093 (room_id), INDEX IDX_8F3F68C5AA7F73B8 (cylinder_key_id), INDEX IDX_8F3F68C52C7C2CBA (workflow_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, text LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_CFBDFA14217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, office_building_id INT DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, middle_initial VARCHAR(255) DEFAULT NULL, netid VARCHAR(255) DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, uin INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, office_number VARCHAR(255) DEFAULT NULL, office_phone VARCHAR(255) DEFAULT NULL, home_address LONGTEXT DEFAULT NULL, work_address LONGTEXT DEFAULT NULL, is_drs_training_complete TINYINT(1) NOT NULL, is_igb_training_complete TINYINT(1) NOT NULL, offer_letter_date DATE DEFAULT NULL, has_given_key_deposit TINYINT(1) NOT NULL, preferred_address VARCHAR(255) NOT NULL, INDEX IDX_34DCD17677C03CA0 (office_building_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room_affiliation (id INT AUTO_INCREMENT NOT NULL, room_id INT NOT NULL, person_id INT NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, INDEX IDX_FE47BB0F54177093 (room_id), INDEX IDX_FE47BB0F217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room_key_affiliation (id INT AUTO_INCREMENT NOT NULL, room_id INT NOT NULL, cylinder_key_id INT NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, INDEX IDX_7259982554177093 (room_id), INDEX IDX_72599825AA7F73B8 (cylinder_key_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supervisor_affiliation (id INT AUTO_INCREMENT NOT NULL, supervisor_id INT NOT NULL, supervisee_id INT NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, INDEX IDX_7681703619E9AC5F (supervisor_id), INDEX IDX_768170369E97DBD8 (supervisee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, short_name VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, is_non_research TINYINT(1) NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE theme_affiliation (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, theme_id INT NOT NULL, member_category_id INT NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, INDEX IDX_259063F217BBB47 (person_id), INDEX IDX_259063F59027487 (theme_id), INDEX IDX_259063FCB7B8920 (member_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE theme_leader_affiliation (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, theme_id INT NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, INDEX IDX_8F15C737217BBB47 (person_id), INDEX IDX_8F15C73759027487 (theme_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workflow (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workflow_progress (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, workflow_step_id INT NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, INDEX IDX_E9620D5C217BBB47 (person_id), INDEX IDX_E9620D5C71FE882C (workflow_step_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workflow_step (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workflow_step_category (id INT AUTO_INCREMENT NOT NULL, workflow_step_id INT NOT NULL, member_category_id INT NOT NULL, position INT NOT NULL, INDEX IDX_A083E98071FE882C (workflow_step_id), INDEX IDX_A083E980CB7B8920 (member_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A770124B2 FOREIGN KEY (college_id) REFERENCES college (id)');
        $this->addSql('ALTER TABLE department_affiliation ADD CONSTRAINT FK_435CB26F217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE department_affiliation ADD CONSTRAINT FK_435CB26FAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE key_affiliation ADD CONSTRAINT FK_B11C1673217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE key_affiliation ADD CONSTRAINT FK_B11C1673AA7F73B8 FOREIGN KEY (cylinder_key_id) REFERENCES `key` (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C559027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C554177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5AA7F73B8 FOREIGN KEY (cylinder_key_id) REFERENCES `key` (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C52C7C2CBA FOREIGN KEY (workflow_id) REFERENCES workflow (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD17677C03CA0 FOREIGN KEY (office_building_id) REFERENCES building (id)');
        $this->addSql('ALTER TABLE room_affiliation ADD CONSTRAINT FK_FE47BB0F54177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE room_affiliation ADD CONSTRAINT FK_FE47BB0F217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE room_key_affiliation ADD CONSTRAINT FK_7259982554177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE room_key_affiliation ADD CONSTRAINT FK_72599825AA7F73B8 FOREIGN KEY (cylinder_key_id) REFERENCES `key` (id)');
        $this->addSql('ALTER TABLE supervisor_affiliation ADD CONSTRAINT FK_7681703619E9AC5F FOREIGN KEY (supervisor_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE supervisor_affiliation ADD CONSTRAINT FK_768170369E97DBD8 FOREIGN KEY (supervisee_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE theme_affiliation ADD CONSTRAINT FK_259063F217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE theme_affiliation ADD CONSTRAINT FK_259063F59027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE theme_affiliation ADD CONSTRAINT FK_259063FCB7B8920 FOREIGN KEY (member_category_id) REFERENCES member_category (id)');
        $this->addSql('ALTER TABLE theme_leader_affiliation ADD CONSTRAINT FK_8F15C737217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE theme_leader_affiliation ADD CONSTRAINT FK_8F15C73759027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE workflow_progress ADD CONSTRAINT FK_E9620D5C217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE workflow_progress ADD CONSTRAINT FK_E9620D5C71FE882C FOREIGN KEY (workflow_step_id) REFERENCES workflow_step (id)');
        $this->addSql('ALTER TABLE workflow_step_category ADD CONSTRAINT FK_A083E98071FE882C FOREIGN KEY (workflow_step_id) REFERENCES workflow_step (id)');
        $this->addSql('ALTER TABLE workflow_step_category ADD CONSTRAINT FK_A083E980CB7B8920 FOREIGN KEY (member_category_id) REFERENCES member_category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD17677C03CA0');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A770124B2');
        $this->addSql('ALTER TABLE department_affiliation DROP FOREIGN KEY FK_435CB26FAE80F5DF');
        $this->addSql('ALTER TABLE key_affiliation DROP FOREIGN KEY FK_B11C1673AA7F73B8');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5AA7F73B8');
        $this->addSql('ALTER TABLE room_key_affiliation DROP FOREIGN KEY FK_72599825AA7F73B8');
        $this->addSql('ALTER TABLE theme_affiliation DROP FOREIGN KEY FK_259063FCB7B8920');
        $this->addSql('ALTER TABLE workflow_step_category DROP FOREIGN KEY FK_A083E980CB7B8920');
        $this->addSql('ALTER TABLE department_affiliation DROP FOREIGN KEY FK_435CB26F217BBB47');
        $this->addSql('ALTER TABLE key_affiliation DROP FOREIGN KEY FK_B11C1673217BBB47');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5A76ED395');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5217BBB47');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14217BBB47');
        $this->addSql('ALTER TABLE room_affiliation DROP FOREIGN KEY FK_FE47BB0F217BBB47');
        $this->addSql('ALTER TABLE supervisor_affiliation DROP FOREIGN KEY FK_7681703619E9AC5F');
        $this->addSql('ALTER TABLE supervisor_affiliation DROP FOREIGN KEY FK_768170369E97DBD8');
        $this->addSql('ALTER TABLE theme_affiliation DROP FOREIGN KEY FK_259063F217BBB47');
        $this->addSql('ALTER TABLE theme_leader_affiliation DROP FOREIGN KEY FK_8F15C737217BBB47');
        $this->addSql('ALTER TABLE workflow_progress DROP FOREIGN KEY FK_E9620D5C217BBB47');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C554177093');
        $this->addSql('ALTER TABLE room_affiliation DROP FOREIGN KEY FK_FE47BB0F54177093');
        $this->addSql('ALTER TABLE room_key_affiliation DROP FOREIGN KEY FK_7259982554177093');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C559027487');
        $this->addSql('ALTER TABLE theme_affiliation DROP FOREIGN KEY FK_259063F59027487');
        $this->addSql('ALTER TABLE theme_leader_affiliation DROP FOREIGN KEY FK_8F15C73759027487');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C52C7C2CBA');
        $this->addSql('ALTER TABLE workflow_progress DROP FOREIGN KEY FK_E9620D5C71FE882C');
        $this->addSql('ALTER TABLE workflow_step_category DROP FOREIGN KEY FK_A083E98071FE882C');
        $this->addSql('DROP TABLE building');
        $this->addSql('DROP TABLE college');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE department_affiliation');
        $this->addSql('DROP TABLE `key`');
        $this->addSql('DROP TABLE key_affiliation');
        $this->addSql('DROP TABLE log');
        $this->addSql('DROP TABLE member_category');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE room_affiliation');
        $this->addSql('DROP TABLE room_key_affiliation');
        $this->addSql('DROP TABLE supervisor_affiliation');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE theme_affiliation');
        $this->addSql('DROP TABLE theme_leader_affiliation');
        $this->addSql('DROP TABLE workflow');
        $this->addSql('DROP TABLE workflow_progress');
        $this->addSql('DROP TABLE workflow_step');
        $this->addSql('DROP TABLE workflow_step_category');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
