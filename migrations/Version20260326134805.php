<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260326134805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE badge ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE badge ADD CONSTRAINT FK_FEF0481DFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEF0481DFB88E14F ON badge (utilisateur_id)');
        $this->addSql('ALTER TABLE code ADD code_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE code ADD CONSTRAINT FK_7715309827DAFE17 FOREIGN KEY (code_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7715309827DAFE17 ON code (code_id)');
        $this->addSql('ALTER TABLE qrcode ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE qrcode ADD CONSTRAINT FK_A4FF23ECFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A4FF23ECFB88E14F ON qrcode (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE badge DROP FOREIGN KEY FK_FEF0481DFB88E14F');
        $this->addSql('DROP INDEX UNIQ_FEF0481DFB88E14F ON badge');
        $this->addSql('ALTER TABLE badge DROP utilisateur_id');
        $this->addSql('ALTER TABLE code DROP FOREIGN KEY FK_7715309827DAFE17');
        $this->addSql('DROP INDEX UNIQ_7715309827DAFE17 ON code');
        $this->addSql('ALTER TABLE code DROP code_id');
        $this->addSql('ALTER TABLE qrcode DROP FOREIGN KEY FK_A4FF23ECFB88E14F');
        $this->addSql('DROP INDEX UNIQ_A4FF23ECFB88E14F ON qrcode');
        $this->addSql('ALTER TABLE qrcode DROP utilisateur_id');
    }
}
