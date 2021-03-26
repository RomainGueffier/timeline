<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210212192055 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE timeline (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, visibility TINYINT(1) NOT NULL, INDEX IDX_46FEC666A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE timeline ADD CONSTRAINT FK_46FEC666A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE category ADD timeline_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1EDBEDD37 FOREIGN KEY (timeline_id) REFERENCES timeline (id)');
        $this->addSql('CREATE INDEX IDX_64C19C1EDBEDD37 ON category (timeline_id)');
        $this->addSql('ALTER TABLE `character` ADD timeline_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB034EDBEDD37 FOREIGN KEY (timeline_id) REFERENCES timeline (id)');
        $this->addSql('CREATE INDEX IDX_937AB034EDBEDD37 ON `character` (timeline_id)');
        $this->addSql('ALTER TABLE event ADD timeline_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7EDBEDD37 FOREIGN KEY (timeline_id) REFERENCES timeline (id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7EDBEDD37 ON event (timeline_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1EDBEDD37');
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB034EDBEDD37');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7EDBEDD37');
        $this->addSql('DROP TABLE timeline');
        $this->addSql('DROP INDEX IDX_64C19C1EDBEDD37 ON category');
        $this->addSql('ALTER TABLE category DROP timeline_id');
        $this->addSql('DROP INDEX IDX_937AB034EDBEDD37 ON `character`');
        $this->addSql('ALTER TABLE `character` DROP timeline_id');
        $this->addSql('DROP INDEX IDX_3BAE0AA7EDBEDD37 ON event');
        $this->addSql('ALTER TABLE event DROP timeline_id');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
