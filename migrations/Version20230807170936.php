<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\SupervisorAffiliation;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230807170936 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $supervisorRepository = $em->getRepository(SupervisorAffiliation::class);
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sponsor_affiliation (id INT AUTO_INCREMENT NOT NULL, sponsor_id INT NOT NULL, sponsee_theme_affiliation_id INT NOT NULL, started_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_BC86462B12F7FB51 (sponsor_id), INDEX IDX_BC86462B9340102E (sponsee_theme_affiliation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sponsor_affiliation ADD CONSTRAINT FK_BC86462B12F7FB51 FOREIGN KEY (sponsor_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE sponsor_affiliation ADD CONSTRAINT FK_BC86462B9340102E FOREIGN KEY (sponsee_theme_affiliation_id) REFERENCES theme_affiliation (id)');
        $this->addSql('ALTER TABLE supervisor_affiliation DROP FOREIGN KEY FK_768170369E97DBD8');
        $this->addSql('DROP INDEX IDX_768170369E97DBD8 ON supervisor_affiliation');

        // Change each supervisee id to point to a theme affiliation instead (?)
        $this->addSql('alter table supervisor_affiliation change supervisee_id supervisee_id int default null');
        $this->addSql('update supervisor_affiliation s1 set s1.supervisee_id=
(select t.id from supervisor_affiliation s join theme_affiliation t on t.person_id=s.supervisee_id
where s.id = s1.id
and (t.started_at is null or s.ended_at is null or s.ended_at > t.started_at) 
and (t.ended_at is null or s.started_at is null or t.ended_at > s.started_at)
limit 1)');
        $this->addSql('delete from supervisor_affiliation where supervisee_id is null');

        $this->addSql('ALTER TABLE supervisor_affiliation CHANGE supervisee_id supervisee_theme_affiliation_id INT NOT NULL');
        $this->addSql('ALTER TABLE supervisor_affiliation ADD CONSTRAINT FK_76817036F8B9ECAE FOREIGN KEY (supervisee_theme_affiliation_id) REFERENCES theme_affiliation (id)');
        $this->addSql('CREATE INDEX IDX_76817036F8B9ECAE ON supervisor_affiliation (supervisee_theme_affiliation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sponsor_affiliation DROP FOREIGN KEY FK_BC86462B12F7FB51');
        $this->addSql('ALTER TABLE sponsor_affiliation DROP FOREIGN KEY FK_BC86462B9340102E');
        $this->addSql('DROP TABLE sponsor_affiliation');
        $this->addSql('ALTER TABLE supervisor_affiliation DROP FOREIGN KEY FK_76817036F8B9ECAE');
        $this->addSql('DROP INDEX IDX_76817036F8B9ECAE ON supervisor_affiliation');
        $this->addSql('ALTER TABLE supervisor_affiliation CHANGE supervisee_theme_affiliation_id supervisee_id INT NOT NULL');
        $this->addSql('ALTER TABLE supervisor_affiliation ADD CONSTRAINT FK_768170369E97DBD8 FOREIGN KEY (supervisee_id) REFERENCES person (id)');
        $this->addSql('CREATE INDEX IDX_768170369E97DBD8 ON supervisor_affiliation (supervisee_id)');
    }
}
