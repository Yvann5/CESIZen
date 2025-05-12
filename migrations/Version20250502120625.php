<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250502120625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat_diagnostic ADD questionnaire_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat_diagnostic ADD CONSTRAINT FK_B5C39CF3CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B5C39CF3CE07E8FF ON resultat_diagnostic (questionnaire_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat_diagnostic DROP FOREIGN KEY FK_B5C39CF3CE07E8FF
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_B5C39CF3CE07E8FF ON resultat_diagnostic
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat_diagnostic DROP questionnaire_id
        SQL);
    }
}
