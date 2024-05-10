<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240510130455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert RecurrenceEnum values into recurrence table';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO recurrence (id, name) VALUES (nextval('recurrence_id_seq'), 'daily')");
        $this->addSql("INSERT INTO recurrence (id, name) VALUES (nextval('recurrence_id_seq'), 'weekly')");
        $this->addSql("INSERT INTO recurrence (id, name) VALUES (nextval('recurrence_id_seq'), 'monthly')");
        $this->addSql("INSERT INTO recurrence (id, name) VALUES (nextval('recurrence_id_seq'), 'bimonthly')");
        $this->addSql("INSERT INTO recurrence (id, name) VALUES (nextval('recurrence_id_seq'), 'quarterly')");
        $this->addSql("INSERT INTO recurrence (id, name) VALUES (nextval('recurrence_id_seq'), 'yearly')");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DELETE FROM recurrence WHERE name IN ('daily', 'weekly', 'monthly', 'bimonthly', 'quarterly', 'yearly')");
    }
}
