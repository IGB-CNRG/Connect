<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220812141258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE theme_affiliation ADD theme_roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql("UPDATE theme_affiliation SET theme_roles = '[\"theme_leader\"]' WHERE is_theme_leader=1");
        $this->addSql('ALTER TABLE theme_affiliation DROP is_theme_leader, DROP is_theme_admin, DROP is_lab_manager');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE theme_affiliation ADD is_theme_leader TINYINT(1) NOT NULL, ADD is_theme_admin TINYINT(1) NOT NULL, ADD is_lab_manager TINYINT(1) NOT NULL, DROP theme_roles');
    }
}
