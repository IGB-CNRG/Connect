<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230523165226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person ADD unit_id INT DEFAULT NULL, ADD other_unit VARCHAR(255) DEFAULT NULL, ADD position_when_joined VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('CREATE INDEX IDX_34DCD176F8BD700D ON person (unit_id)');

        $this->addSql('UPDATE person inner join unit_affiliation ua on person.id = ua.person_id set person.unit_id=ua.unit_id, person.other_unit=ua.other_unit');

        $this->addSql('ALTER TABLE unit_affiliation DROP FOREIGN KEY FK_71157F16217BBB47');
        $this->addSql('ALTER TABLE unit_affiliation DROP FOREIGN KEY FK_71157F16F8BD700D');
        $this->addSql('DROP TABLE unit_affiliation');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE unit_affiliation (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, unit_id INT DEFAULT NULL, other_unit VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_71157F16217BBB47 (person_id), INDEX IDX_71157F16F8BD700D (unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE unit_affiliation ADD CONSTRAINT FK_71157F16217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE unit_affiliation ADD CONSTRAINT FK_71157F16F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');

        $this->addSql('INSERT into unit_affiliation (person_id, unit_id, other_unit, created_at, updated_at) SELECT id, unit_id, other_unit, NOW() as created_at, NOW() as updated_at from person where unit_id is not null or other_unit is not null');

        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176F8BD700D');
        $this->addSql('DROP INDEX IDX_34DCD176F8BD700D ON person');
        $this->addSql('ALTER TABLE person DROP unit_id, DROP other_unit, DROP position_when_joined');
    }
}
