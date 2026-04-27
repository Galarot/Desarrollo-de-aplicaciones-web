<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220100617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE genero_vinilo (genero_id INT NOT NULL, vinilo_id INT NOT NULL, PRIMARY KEY (genero_id, vinilo_id))');
        $this->addSql('CREATE INDEX IDX_3A89E091BCE7B795 ON genero_vinilo (genero_id)');
        $this->addSql('CREATE INDEX IDX_3A89E091F8E11D70 ON genero_vinilo (vinilo_id)');
        $this->addSql('ALTER TABLE genero_vinilo ADD CONSTRAINT FK_3A89E091BCE7B795 FOREIGN KEY (genero_id) REFERENCES genero (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE genero_vinilo ADD CONSTRAINT FK_3A89E091F8E11D70 FOREIGN KEY (vinilo_id) REFERENCES vinilo (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE genero_vinilo DROP CONSTRAINT FK_3A89E091BCE7B795');
        $this->addSql('ALTER TABLE genero_vinilo DROP CONSTRAINT FK_3A89E091F8E11D70');
        $this->addSql('DROP TABLE genero_vinilo');
    }
}
