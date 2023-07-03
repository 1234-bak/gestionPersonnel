<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230622171546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE personne CHANGE direction_id direction_id INT NOT NULL, CHANGE sousdirection_id sousdirection_id INT NOT NULL, CHANGE service_id service_id INT NOT NULL');
        $this->addSql('ALTER TABLE signature ADD created_at DATETIME DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE personne CHANGE direction_id direction_id INT DEFAULT NULL, CHANGE sousdirection_id sousdirection_id INT DEFAULT NULL, CHANGE service_id service_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE signature DROP created_at, DROP updated_at');
    }
}
