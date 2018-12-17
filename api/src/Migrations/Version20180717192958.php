<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180717192958 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE reminder (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, parent_reminder_id BIGINT UNSIGNED DEFAULT NULL, label VARCHAR(100) NOT NULL, description VARCHAR(255) NOT NULL, amount INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, recurrence_rule LONGTEXT DEFAULT NULL, status VARCHAR(20) DEFAULT \'scheduled\' NOT NULL, INDEX IDX_40374F40B2ABAC05 (parent_reminder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F40B2ABAC05 FOREIGN KEY (parent_reminder_id) REFERENCES reminder (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reminder DROP FOREIGN KEY FK_40374F40B2ABAC05');
        $this->addSql('DROP TABLE reminder');
    }
}
