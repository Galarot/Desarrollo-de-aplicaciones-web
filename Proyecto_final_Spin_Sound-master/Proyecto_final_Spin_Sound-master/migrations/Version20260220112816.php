<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220112816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detalle_carrito ADD vinilo_id INT NOT NULL');
        $this->addSql('ALTER TABLE detalle_carrito ADD CONSTRAINT FK_3F38507DF8E11D70 FOREIGN KEY (vinilo_id) REFERENCES vinilo (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_3F38507DF8E11D70 ON detalle_carrito (vinilo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detalle_carrito DROP CONSTRAINT FK_3F38507DF8E11D70');
        $this->addSql('DROP INDEX IDX_3F38507DF8E11D70');
        $this->addSql('ALTER TABLE detalle_carrito DROP vinilo_id');
    }
}
