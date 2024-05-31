<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240528135929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE theme_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, is_member TINYINT(1) NOT NULL, display_in_directory TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO `theme_type` (id, name, is_member, display_in_directory) VALUES (1,\'Theme\',1,1),(2,\'Staff Group\',1,1),(3,\'External Group\',0,0),(4,\'Affiliated Research Group\',0,1)');
        $this->addSql('ALTER TABLE theme ADD theme_type_id INT NOT NULL');
        $this->addSql('UPDATE theme SET theme_type_id=1 where is_non_research=0 and is_outside_group=0');
        $this->addSql('UPDATE theme SET theme_type_id=2 where is_non_research=1 and is_outside_group=0');
        $this->addSql('UPDATE theme SET theme_type_id=3 where is_non_research=0 and is_outside_group=1');
        $this->addSql('ALTER TABLE theme DROP is_non_research, DROP is_outside_group');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E7089C2F89B0 FOREIGN KEY (theme_type_id) REFERENCES theme_type (id)');
        $this->addSql('CREATE INDEX IDX_9775E7089C2F89B0 ON theme (theme_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE theme DROP FOREIGN KEY FK_9775E7089C2F89B0');
        $this->addSql('DROP INDEX IDX_9775E7089C2F89B0 ON theme');
        $this->addSql('ALTER TABLE theme ADD is_non_research TINYINT(1) NOT NULL, ADD is_outside_group TINYINT(1) NOT NULL');
        $this->addSql('UPDATE theme set is_non_research=0, is_outside_group=0 where theme_type_id=1');
        $this->addSql('UPDATE theme set is_non_research=1, is_outside_group=0 where theme_type_id=2');
        $this->addSql('UPDATE theme set is_non_research=0, is_outside_group=1 where theme_type_id=3');
        $this->addSql('UPDATE theme set is_non_research=0, is_outside_group=0 where theme_type_id=4');
        $this->addSql('DROP TABLE theme_type');
        $this->addSql('ALTER TABLE theme DROP theme_type_id');
    }
}
