<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220315171307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE theme_leader_affiliation');
        $this->addSql('ALTER TABLE theme_affiliation ADD is_theme_leader TINYINT(1) NOT NULL, ADD is_theme_admin TINYINT(1) NOT NULL, ADD is_lab_manager TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE theme_leader_affiliation (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, theme_id INT NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, INDEX IDX_8F15C737217BBB47 (person_id), INDEX IDX_8F15C73759027487 (theme_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE theme_leader_affiliation ADD CONSTRAINT FK_8F15C737217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE theme_leader_affiliation ADD CONSTRAINT FK_8F15C73759027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE theme_affiliation DROP is_theme_leader, DROP is_theme_admin, DROP is_lab_manager');
    }
}
