<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260212101335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add slug column to post table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql("UPDATE post SET slug = LOWER(REPLACE(title, ' ', '-')) WHERE slug IS NULL");
        $this->addSql('ALTER TABLE post ALTER COLUMN slug SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A8A6C8D989D9B62 ON post (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_5A8A6C8D989D9B62');
        $this->addSql('ALTER TABLE post DROP slug');
    }
}
