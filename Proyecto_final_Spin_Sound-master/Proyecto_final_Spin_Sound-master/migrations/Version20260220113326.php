<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220113326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cliente ADD carrito_id INT NOT NULL');
        $this->addSql('ALTER TABLE cliente ADD CONSTRAINT FK_F41C9B25DE2CF6E7 FOREIGN KEY (carrito_id) REFERENCES carrito (id) NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F41C9B25DE2CF6E7 ON cliente (carrito_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cliente DROP CONSTRAINT FK_F41C9B25DE2CF6E7');
        $this->addSql('DROP INDEX UNIQ_F41C9B25DE2CF6E7');
        $this->addSql('ALTER TABLE cliente DROP carrito_id');
    }
}
