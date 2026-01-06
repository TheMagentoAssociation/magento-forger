<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260106000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial database schema for Magento Forger';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) DEFAULT NULL,
            github_id VARCHAR(255) NOT NULL,
            github_username VARCHAR(255) DEFAULT NULL,
            is_admin TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATE NOT NULL,
            updated_at DATE NOT NULL,
            UNIQUE INDEX UNIQ_8D93D6495E237E06 (name),
            UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
            UNIQUE INDEX UNIQ_8D93D64959C53621 (github_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS companies (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            website VARCHAR(255) DEFAULT NULL,
            description LONGTEXT DEFAULT NULL,
            logo_path VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATE NOT NULL,
            updated_at DATE NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE IF NOT EXISTS company_affiliations (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT DEFAULT NULL,
            company_id INT DEFAULT NULL,
            role VARCHAR(255) DEFAULT NULL,
            start_date DATE NOT NULL,
            end_date DATE DEFAULT NULL,
            created_at DATE NOT NULL,
            updated_at DATE NOT NULL,
            INDEX IDX_A71B8FD5A76ED395 (user_id),
            INDEX IDX_A71B8FD5979B1AD6 (company_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE company_affiliations ADD CONSTRAINT FK_A71B8FD5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE company_affiliations ADD CONSTRAINT FK_A71B8FD5979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_affiliations DROP FOREIGN KEY FK_A71B8FD5A76ED395');
        $this->addSql('ALTER TABLE company_affiliations DROP FOREIGN KEY FK_A71B8FD5979B1AD6');
        $this->addSql('DROP TABLE company_affiliations');
        $this->addSql('DROP TABLE companies');
        $this->addSql('DROP TABLE users');
    }
}
