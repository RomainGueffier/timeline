<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201019131315 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE characters_categories (character_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_40E98AC51136BE75 (character_id), INDEX IDX_40E98AC512469DE2 (category_id), PRIMARY KEY(character_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE characters_categories ADD CONSTRAINT FK_40E98AC51136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE characters_categories ADD CONSTRAINT FK_40E98AC512469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `character` DROP period');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE characters_categories');
        $this->addSql('ALTER TABLE `character` ADD period INT DEFAULT NULL');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
