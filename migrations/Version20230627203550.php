<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230627203550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE theme_affiliation ADD position_when_joined VARCHAR(255) DEFAULT NULL');
        $this->addSql('update theme_affiliation ta inner join person p on ta.person_id=p.id set ta.position_when_joined=p.position_when_joined where p.position_when_joined is not null');
        $this->addSql('ALTER TABLE person DROP position_when_joined');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person ADD position_when_joined VARCHAR(255) DEFAULT NULL');
        $this->addSql('update person p inner join theme_affiliation ta on ta.person_id=p.id set p.position_when_joined=ta.position_when_joined where ta.position_when_joined is not null');
        $this->addSql('ALTER TABLE theme_affiliation DROP position_when_joined');
    }
}
