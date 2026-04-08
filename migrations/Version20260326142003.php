<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260326142003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log_access ADD code_id INT DEFAULT NULL, ADD qrcode_id INT DEFAULT NULL, ADD badge_id INT DEFAULT NULL, ADD porte_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log_access ADD CONSTRAINT FK_139378F627DAFE17 FOREIGN KEY (code_id) REFERENCES code (id)');
        $this->addSql('ALTER TABLE log_access ADD CONSTRAINT FK_139378F6551274BC FOREIGN KEY (qrcode_id) REFERENCES qrcode (id)');
        $this->addSql('ALTER TABLE log_access ADD CONSTRAINT FK_139378F6F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE log_access ADD CONSTRAINT FK_139378F66BCC8323 FOREIGN KEY (porte_id) REFERENCES porte (id)');
        $this->addSql('CREATE INDEX IDX_139378F627DAFE17 ON log_access (code_id)');
        $this->addSql('CREATE INDEX IDX_139378F6551274BC ON log_access (qrcode_id)');
        $this->addSql('CREATE INDEX IDX_139378F6F7A2C2FC ON log_access (badge_id)');
        $this->addSql('CREATE INDEX IDX_139378F66BCC8323 ON log_access (porte_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log_access DROP FOREIGN KEY FK_139378F627DAFE17');
        $this->addSql('ALTER TABLE log_access DROP FOREIGN KEY FK_139378F6551274BC');
        $this->addSql('ALTER TABLE log_access DROP FOREIGN KEY FK_139378F6F7A2C2FC');
        $this->addSql('ALTER TABLE log_access DROP FOREIGN KEY FK_139378F66BCC8323');
        $this->addSql('DROP INDEX IDX_139378F627DAFE17 ON log_access');
        $this->addSql('DROP INDEX IDX_139378F6551274BC ON log_access');
        $this->addSql('DROP INDEX IDX_139378F6F7A2C2FC ON log_access');
        $this->addSql('DROP INDEX IDX_139378F66BCC8323 ON log_access');
        $this->addSql('ALTER TABLE log_access DROP code_id, DROP qrcode_id, DROP badge_id, DROP porte_id');
    }
}
