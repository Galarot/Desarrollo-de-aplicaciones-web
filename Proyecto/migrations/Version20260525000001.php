<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260525000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add summon crystals and owned character collection to users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD crystals INT DEFAULT 0 NOT NULL, ADD owned_characters JSON DEFAULT NULL');
        $this->addSql("UPDATE `user` SET owned_characters = '[]' WHERE owned_characters IS NULL");
        $this->addSql('ALTER TABLE `user` MODIFY owned_characters JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP crystals, DROP owned_characters');
    }
}
