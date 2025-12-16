<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251128084842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_deposit (user_id INT NOT NULL, deposit_id INT NOT NULL, INDEX IDX_CDD68333A76ED395 (user_id), INDEX IDX_CDD683339815E4B1 (deposit_id), PRIMARY KEY (user_id, deposit_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_deposit ADD CONSTRAINT FK_CDD68333A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_deposit ADD CONSTRAINT FK_CDD683339815E4B1 FOREIGN KEY (deposit_id) REFERENCES deposit (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_deposit DROP FOREIGN KEY FK_CDD68333A76ED395');
        $this->addSql('ALTER TABLE user_deposit DROP FOREIGN KEY FK_CDD683339815E4B1');
        $this->addSql('DROP TABLE user_deposit');
    }
}
