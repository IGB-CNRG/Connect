<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231031211657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE theme_affiliation_theme_role (theme_affiliation_id INT NOT NULL, theme_role_id INT NOT NULL, INDEX IDX_54BDB1E2D9B2949 (theme_affiliation_id), INDEX IDX_54BDB1E28F60278F (theme_role_id), PRIMARY KEY(theme_affiliation_id, theme_role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE theme_role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO theme_role (id, name) values (1,\'Theme Leader\'),(2,\'Theme Admin\'),(3,\'Lab Manager\')');
        $this->addSql('INSERT INTO theme_affiliation_theme_role (theme_affiliation_id, theme_role_id) SELECT id, 1 from theme_affiliation where theme_roles LIKE \'%theme_leader%\'');
        $this->addSql('INSERT INTO theme_affiliation_theme_role (theme_affiliation_id, theme_role_id) SELECT id, 2 from theme_affiliation where theme_roles LIKE \'%theme_admin%\'');
        $this->addSql('INSERT INTO theme_affiliation_theme_role (theme_affiliation_id, theme_role_id) SELECT id, 3 from theme_affiliation where theme_roles LIKE \'%lab_manager%\'');
        $this->addSql('ALTER TABLE theme_affiliation_theme_role ADD CONSTRAINT FK_54BDB1E2D9B2949 FOREIGN KEY (theme_affiliation_id) REFERENCES theme_affiliation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE theme_affiliation_theme_role ADD CONSTRAINT FK_54BDB1E28F60278F FOREIGN KEY (theme_role_id) REFERENCES theme_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE theme_affiliation DROP theme_roles');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE theme_affiliation_theme_role DROP FOREIGN KEY FK_54BDB1E2D9B2949');
        $this->addSql('ALTER TABLE theme_affiliation_theme_role DROP FOREIGN KEY FK_54BDB1E28F60278F');
        $this->addSql('DROP TABLE theme_affiliation_theme_role');
        $this->addSql('DROP TABLE theme_role');
        $this->addSql('ALTER TABLE theme_affiliation ADD theme_roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
