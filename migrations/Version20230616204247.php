<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230616204247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unit_affiliation DROP FOREIGN KEY FK_71157F16F8BD700D');
        $this->addSql('ALTER TABLE unit_affiliation DROP FOREIGN KEY FK_71157F16217BBB47');
        $this->addSql('DROP TABLE unit_affiliation');
        $this->addSql('ALTER TABLE workflow_notification ADD is_all_member_categories TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE unit_affiliation (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, unit_id INT DEFAULT NULL, other_unit VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_71157F16217BBB47 (person_id), INDEX IDX_71157F16F8BD700D (unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE unit_affiliation ADD CONSTRAINT FK_71157F16F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('ALTER TABLE unit_affiliation ADD CONSTRAINT FK_71157F16217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE workflow_notification DROP is_all_member_categories');
    }
}
