<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220102350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detalle_pedido DROP CONSTRAINT fk_a834f569c861d91d');
        $this->addSql('ALTER TABLE detalle_pedido DROP CONSTRAINT fk_a834f56978d4a157');
        $this->addSql('DROP INDEX idx_a834f56978d4a157');
        $this->addSql('DROP INDEX idx_a834f569c861d91d');
        $this->addSql('ALTER TABLE detalle_pedido ADD vinilo_id INT NOT NULL');
        $this->addSql('ALTER TABLE detalle_pedido ADD pedido_id INT NOT NULL');
        $this->addSql('ALTER TABLE detalle_pedido DROP id_vinilo_id');
        $this->addSql('ALTER TABLE detalle_pedido DROP id_pedido_id');
        $this->addSql('ALTER TABLE detalle_pedido ADD CONSTRAINT FK_A834F569F8E11D70 FOREIGN KEY (vinilo_id) REFERENCES vinilo (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE detalle_pedido ADD CONSTRAINT FK_A834F5694854653A FOREIGN KEY (pedido_id) REFERENCES pedido (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_A834F569F8E11D70 ON detalle_pedido (vinilo_id)');
        $this->addSql('CREATE INDEX IDX_A834F5694854653A ON detalle_pedido (pedido_id)');
        $this->addSql('ALTER TABLE pedido DROP CONSTRAINT fk_c4ec16ce7bf9ce86');
        $this->addSql('DROP INDEX idx_c4ec16ce7bf9ce86');
        $this->addSql('ALTER TABLE pedido RENAME COLUMN id_cliente_id TO cliente_id');
        $this->addSql('ALTER TABLE pedido ADD CONSTRAINT FK_C4EC16CEDE734E51 FOREIGN KEY (cliente_id) REFERENCES cliente (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_C4EC16CEDE734E51 ON pedido (cliente_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detalle_pedido DROP CONSTRAINT FK_A834F569F8E11D70');
        $this->addSql('ALTER TABLE detalle_pedido DROP CONSTRAINT FK_A834F5694854653A');
        $this->addSql('DROP INDEX IDX_A834F569F8E11D70');
        $this->addSql('DROP INDEX IDX_A834F5694854653A');
        $this->addSql('ALTER TABLE detalle_pedido ADD id_vinilo_id INT NOT NULL');
        $this->addSql('ALTER TABLE detalle_pedido ADD id_pedido_id INT NOT NULL');
        $this->addSql('ALTER TABLE detalle_pedido DROP vinilo_id');
        $this->addSql('ALTER TABLE detalle_pedido DROP pedido_id');
        $this->addSql('ALTER TABLE detalle_pedido ADD CONSTRAINT fk_a834f569c861d91d FOREIGN KEY (id_pedido_id) REFERENCES pedido (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE detalle_pedido ADD CONSTRAINT fk_a834f56978d4a157 FOREIGN KEY (id_vinilo_id) REFERENCES vinilo (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_a834f56978d4a157 ON detalle_pedido (id_vinilo_id)');
        $this->addSql('CREATE INDEX idx_a834f569c861d91d ON detalle_pedido (id_pedido_id)');
        $this->addSql('ALTER TABLE pedido DROP CONSTRAINT FK_C4EC16CEDE734E51');
        $this->addSql('DROP INDEX IDX_C4EC16CEDE734E51');
        $this->addSql('ALTER TABLE pedido RENAME COLUMN cliente_id TO id_cliente_id');
        $this->addSql('ALTER TABLE pedido ADD CONSTRAINT fk_c4ec16ce7bf9ce86 FOREIGN KEY (id_cliente_id) REFERENCES cliente (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_c4ec16ce7bf9ce86 ON pedido (id_cliente_id)');
    }
}
