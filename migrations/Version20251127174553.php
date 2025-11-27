<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127174553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deposit ADD company_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE deposit ADD CONSTRAINT FK_95DB9D39979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_95DB9D39979B1AD6 ON deposit (company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deposit DROP FOREIGN KEY FK_95DB9D39979B1AD6');
        $this->addSql('DROP INDEX IDX_95DB9D39979B1AD6 ON deposit');
        $this->addSql('ALTER TABLE deposit DROP company_id');
    }
}
