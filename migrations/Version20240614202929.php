<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240614202929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE theme_person (theme_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_E4E4E50A59027487 (theme_id), INDEX IDX_E4E4E50A217BBB47 (person_id), PRIMARY KEY(theme_id, person_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE theme_person ADD CONSTRAINT FK_E4E4E50A59027487 FOREIGN KEY (theme_id) REFERENCES theme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE theme_person ADD CONSTRAINT FK_E4E4E50A217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE theme_person DROP FOREIGN KEY FK_E4E4E50A59027487');
        $this->addSql('ALTER TABLE theme_person DROP FOREIGN KEY FK_E4E4E50A217BBB47');
        $this->addSql('DROP TABLE theme_person');
    }
}
