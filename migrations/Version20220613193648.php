<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220613193648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE building ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE college ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE department ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql(
            'ALTER TABLE department_affiliation ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL'
        );
        $this->addSql('ALTER TABLE `key` ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE key_affiliation ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE member_category ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE room ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql(
            'ALTER TABLE room_affiliation ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE room_key_affiliation ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE supervisor_affiliation ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL'
        );
        $this->addSql('ALTER TABLE theme ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql(
            'ALTER TABLE theme_affiliation ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL'
        );
        $this->addSql('ALTER TABLE workflow ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql(
            'ALTER TABLE workflow_progress ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL'
        );
        $this->addSql('ALTER TABLE workflow_step ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql(
            'ALTER TABLE workflow_step_category ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE building DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE college DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE department DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE department_affiliation DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE `key` DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE key_affiliation DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE member_category DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE room DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE room_affiliation DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE room_key_affiliation DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE supervisor_affiliation DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE theme DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE theme_affiliation DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE workflow DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE workflow_progress DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE workflow_step DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE workflow_step_category DROP created_at, DROP updated_at');
    }
}
