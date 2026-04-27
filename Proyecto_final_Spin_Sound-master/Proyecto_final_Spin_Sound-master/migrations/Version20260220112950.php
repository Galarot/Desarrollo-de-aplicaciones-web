<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220112950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detalle_carrito ADD carrito_id INT NOT NULL');
        $this->addSql('ALTER TABLE detalle_carrito ADD CONSTRAINT FK_3F38507DDE2CF6E7 FOREIGN KEY (carrito_id) REFERENCES carrito (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_3F38507DDE2CF6E7 ON detalle_carrito (carrito_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detalle_carrito DROP CONSTRAINT FK_3F38507DDE2CF6E7');
        $this->addSql('DROP INDEX IDX_3F38507DDE2CF6E7');
        $this->addSql('ALTER TABLE detalle_carrito DROP carrito_id');
    }
}
