<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260326135257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code DROP FOREIGN KEY `FK_7715309827DAFE17`');
        $this->addSql('DROP INDEX UNIQ_7715309827DAFE17 ON code');
        $this->addSql('ALTER TABLE code CHANGE code_id utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE code ADD CONSTRAINT FK_77153098FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_77153098FB88E14F ON code (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code DROP FOREIGN KEY FK_77153098FB88E14F');
        $this->addSql('DROP INDEX UNIQ_77153098FB88E14F ON code');
        $this->addSql('ALTER TABLE code CHANGE utilisateur_id code_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE code ADD CONSTRAINT `FK_7715309827DAFE17` FOREIGN KEY (code_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7715309827DAFE17 ON code (code_id)');
    }
}
