<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230330164438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $names = ['Aurora', 'Bubbles', 'Crystal', 'Galaxy', 'Estrella'];

        foreach($names as $name) {
            $this->connection->executeStatement(
                'INSERT INTO unicorn (name) VALUES (:name)', [
                    "name" => $name
                ]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->connection->executeStatement('TRUNCATE TABLE unicorn');
    }
}
