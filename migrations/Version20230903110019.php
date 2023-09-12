<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230903110019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declaration ADD fichiernaiss VARCHAR(255) DEFAULT NULL, ADD fichierdeces VARCHAR(255) DEFAULT NULL');
        // $this->addSql('ALTER TABLE personne CHANGE direction_id direction_id INT NOT NULL, CHANGE sousdirection_id sousdirection_id INT NOT NULL, CHANGE service_id service_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declaration DROP fichiernaiss, DROP fichierdeces');
        // $this->addSql('ALTER TABLE personne CHANGE direction_id direction_id INT DEFAULT NULL, CHANGE sousdirection_id sousdirection_id INT DEFAULT NULL, CHANGE service_id service_id INT DEFAULT NULL');
    }
}
