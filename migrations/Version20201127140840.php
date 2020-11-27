<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201127140840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE scene ADD content_image_name VARCHAR(255) DEFAULT NULL, ADD content_image_original_name VARCHAR(255) DEFAULT NULL, ADD content_image_mime_type VARCHAR(255) DEFAULT NULL, ADD content_image_size INT DEFAULT NULL, ADD content_image_dimensions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE scene DROP content_image_name, DROP content_image_original_name, DROP content_image_mime_type, DROP content_image_size, DROP content_image_dimensions');
    }
}
