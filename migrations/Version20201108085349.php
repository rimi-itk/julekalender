<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201108085349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', created_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', updated_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649B03A8386 (created_by_id), INDEX IDX_8D93D649896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE calendar ADD created_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD updated_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE calendar ADD CONSTRAINT FK_6EA9A146B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE calendar ADD CONSTRAINT FK_6EA9A146896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_6EA9A146B03A8386 ON calendar (created_by_id)');
        $this->addSql('CREATE INDEX IDX_6EA9A146896DBBDE ON calendar (updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE calendar DROP FOREIGN KEY FK_6EA9A146B03A8386');
        $this->addSql('ALTER TABLE calendar DROP FOREIGN KEY FK_6EA9A146896DBBDE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B03A8386');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649896DBBDE');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP INDEX IDX_6EA9A146B03A8386 ON calendar');
        $this->addSql('DROP INDEX IDX_6EA9A146896DBBDE ON calendar');
        $this->addSql('ALTER TABLE calendar DROP created_by_id, DROP updated_by_id');
    }
}
