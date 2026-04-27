<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220092843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artista_vinilo (artista_id INT NOT NULL, vinilo_id INT NOT NULL, PRIMARY KEY (artista_id, vinilo_id))');
        $this->addSql('CREATE INDEX IDX_AE1A8A45AEB0CF13 ON artista_vinilo (artista_id)');
        $this->addSql('CREATE INDEX IDX_AE1A8A45F8E11D70 ON artista_vinilo (vinilo_id)');
        $this->addSql('ALTER TABLE artista_vinilo ADD CONSTRAINT FK_AE1A8A45AEB0CF13 FOREIGN KEY (artista_id) REFERENCES artista (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artista_vinilo ADD CONSTRAINT FK_AE1A8A45F8E11D70 FOREIGN KEY (vinilo_id) REFERENCES vinilo (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artista_vinilo DROP CONSTRAINT FK_AE1A8A45AEB0CF13');
        $this->addSql('ALTER TABLE artista_vinilo DROP CONSTRAINT FK_AE1A8A45F8E11D70');
        $this->addSql('DROP TABLE artista_vinilo');
    }
}
