<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416135613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE contenu ADD utilisateur_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contenu ADD CONSTRAINT FK_89C2003FFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_89C2003FFB88E14F ON contenu (utilisateur_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE contenu DROP FOREIGN KEY FK_89C2003FFB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_89C2003FFB88E14F ON contenu
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contenu DROP utilisateur_id
        SQL);
    }
}
