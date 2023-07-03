<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230622155716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE personne CHANGE direction_id direction_id INT NOT NULL, CHANGE sousdirection_id sousdirection_id INT NOT NULL, CHANGE service_id service_id INT NOT NULL');
        $this->addSql('ALTER TABLE signature ADD personne_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE signature ADD CONSTRAINT FK_AE880141A21BD112 FOREIGN KEY (personne_id) REFERENCES personne (id)');
        $this->addSql('CREATE INDEX IDX_AE880141A21BD112 ON signature (personne_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE personne CHANGE direction_id direction_id INT DEFAULT NULL, CHANGE sousdirection_id sousdirection_id INT DEFAULT NULL, CHANGE service_id service_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE signature DROP FOREIGN KEY FK_AE880141A21BD112');
        $this->addSql('DROP INDEX IDX_AE880141A21BD112 ON signature');
        $this->addSql('ALTER TABLE signature DROP personne_id');
    }
}
