<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230904103719 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE permission ADD etatcs VARCHAR(255) DEFAULT NULL, ADD etatsd VARCHAR(255) DEFAULT NULL, ADD etatdir VARCHAR(255) DEFAULT NULL, ADD etatdircab VARCHAR(255) DEFAULT NULL');
        // $this->addSql('ALTER TABLE personne CHANGE direction_id direction_id INT NOT NULL, CHANGE sousdirection_id sousdirection_id INT NOT NULL, CHANGE service_id service_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE permission DROP etatcs, DROP etatsd, DROP etatdir, DROP etatdircab');
        // $this->addSql('ALTER TABLE personne CHANGE direction_id direction_id INT DEFAULT NULL, CHANGE sousdirection_id sousdirection_id INT DEFAULT NULL, CHANGE service_id service_id INT DEFAULT NULL');
    }
}
