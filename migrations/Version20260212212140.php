<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212212140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_liked_posts (user_id INT NOT NULL, post_id INT NOT NULL, PRIMARY KEY (user_id, post_id))');
        $this->addSql('CREATE INDEX IDX_3FC37DA9A76ED395 ON user_liked_posts (user_id)');
        $this->addSql('CREATE INDEX IDX_3FC37DA94B89032C ON user_liked_posts (post_id)');
        $this->addSql('ALTER TABLE user_liked_posts ADD CONSTRAINT FK_3FC37DA9A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_liked_posts ADD CONSTRAINT FK_3FC37DA94B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_liked_posts DROP CONSTRAINT FK_3FC37DA9A76ED395');
        $this->addSql('ALTER TABLE user_liked_posts DROP CONSTRAINT FK_3FC37DA94B89032C');
        $this->addSql('DROP TABLE user_liked_posts');
    }
}
