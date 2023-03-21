<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230321194530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5AE80F5DF');
        $this->addSql('CREATE TABLE unit (id INT AUTO_INCREMENT NOT NULL, parent_unit_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, short_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_DCBB0C538AF5044B (parent_unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE unit_affiliation (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, unit_id INT DEFAULT NULL, other_unit VARCHAR(255) DEFAULT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_71157F16217BBB47 (person_id), INDEX IDX_71157F16F8BD700D (unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C538AF5044B FOREIGN KEY (parent_unit_id) REFERENCES unit (id)');
        $this->addSql('ALTER TABLE unit_affiliation ADD CONSTRAINT FK_71157F16217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE unit_affiliation ADD CONSTRAINT FK_71157F16F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('ALTER TABLE department_affiliation DROP FOREIGN KEY FK_435CB26F217BBB47');
        $this->addSql('ALTER TABLE department_affiliation DROP FOREIGN KEY FK_435CB26FAE80F5DF');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A770124B2');

        $this->addSql('INSERT INTO unit (id, name, short_name, created_at, updated_at) SELECT id, name, short_name, created_at, updated_at from department');
        $this->addSql('INSERT INTO unit (name, short_name, created_at, updated_at) SELECT name, abbreviation, created_at, updated_at from college');
        $this->addSql('INSERT INTO unit_affiliation (person_id, unit_id, other_unit, started_at, ended_at, created_at, updated_at) SELECT person_id, department_id, other_department, started_at, ended_at, created_at, updated_at from department_affiliation');

        $this->addSql('DROP TABLE college');
        $this->addSql('DROP TABLE department_affiliation');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP INDEX IDX_8F3F68C5AE80F5DF ON log');
        $this->addSql('ALTER TABLE log CHANGE department_id unit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5F8BD700D ON log (unit_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5F8BD700D');
        $this->addSql('CREATE TABLE college (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, abbreviation VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE department_affiliation (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, department_id INT DEFAULT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, other_department VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_435CB26FAE80F5DF (department_id), INDEX IDX_435CB26F217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE department (id INT AUTO_INCREMENT NOT NULL, college_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, short_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_CD1DE18A770124B2 (college_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE department_affiliation ADD CONSTRAINT FK_435CB26F217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE department_affiliation ADD CONSTRAINT FK_435CB26FAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A770124B2 FOREIGN KEY (college_id) REFERENCES college (id)');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C538AF5044B');
        $this->addSql('ALTER TABLE unit_affiliation DROP FOREIGN KEY FK_71157F16217BBB47');
        $this->addSql('ALTER TABLE unit_affiliation DROP FOREIGN KEY FK_71157F16F8BD700D');
        $this->addSql('DROP TABLE unit');
        $this->addSql('DROP TABLE unit_affiliation');
        $this->addSql('DROP INDEX IDX_8F3F68C5F8BD700D ON log');
        $this->addSql('ALTER TABLE log CHANGE unit_id department_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5AE80F5DF ON log (department_id)');
    }
}
