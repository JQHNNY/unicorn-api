<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230329220414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ADD unicorn_id INT NOT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D2AF80346 FOREIGN KEY (unicorn_id) REFERENCES unicorn (id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D2AF80346 ON post (unicorn_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D2AF80346');
        $this->addSql('DROP INDEX IDX_5A8A6C8D2AF80346 ON post');
        $this->addSql('ALTER TABLE post DROP unicorn_id');
    }
}
