<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220718200328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE key_room (key_id INT NOT NULL, room_id INT NOT NULL, INDEX IDX_F4988A52D145533 (key_id), INDEX IDX_F4988A5254177093 (room_id), PRIMARY KEY(key_id, room_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE key_room ADD CONSTRAINT FK_F4988A52D145533 FOREIGN KEY (key_id) REFERENCES `key` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE key_room ADD CONSTRAINT FK_F4988A5254177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE room_key_affiliation');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE room_key_affiliation (id INT AUTO_INCREMENT NOT NULL, room_id INT NOT NULL, cylinder_key_id INT NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_7259982554177093 (room_id), INDEX IDX_72599825AA7F73B8 (cylinder_key_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE room_key_affiliation ADD CONSTRAINT FK_7259982554177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE room_key_affiliation ADD CONSTRAINT FK_72599825AA7F73B8 FOREIGN KEY (cylinder_key_id) REFERENCES `key` (id)');
        $this->addSql('DROP TABLE key_room');
    }
}
