<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110131907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change card number from integer to varchar to support alphanumeric card numbers';
    }

    public function up(Schema $schema): void
    {
        // Change card number type from integer to varchar(50)
        $this->addSql('ALTER TABLE cards ALTER number TYPE VARCHAR(50)');
    }

    public function down(Schema $schema): void
    {
        // Revert card number type back to integer
        $this->addSql('ALTER TABLE cards ALTER number TYPE INT USING number::integer');
    }
}
