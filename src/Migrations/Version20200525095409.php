<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200525095409 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, trip_id INT NOT NULL, seats INT NOT NULL, created DATETIME NOT NULL, INDEX IDX_E00CEDDEA76ED395 (user_id), INDEX IDX_E00CEDDEA5BC2E0E (trip_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, longitude DOUBLE PRECISION NOT NULL, latitude DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, trip_id INT NOT NULL, created DATETIME NOT NULL, text TINYTEXT NOT NULL, INDEX IDX_9474526CA76ED395 (user_id), INDEX IDX_9474526CA5BC2E0E (trip_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trip (id INT AUTO_INCREMENT NOT NULL, from_c_id INT NOT NULL, to_c_id INT NOT NULL, driver_id INT NOT NULL, start DATETIME NOT NULL, end DATETIME NOT NULL, max_seats INT NOT NULL, seat_price DOUBLE PRECISION NOT NULL, created DATETIME NOT NULL, INDEX IDX_7656F53BE4414630 (from_c_id), INDEX IDX_7656F53B76398A1C (to_c_id), INDEX IDX_7656F53BC3423909 (driver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, login VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, surname VARCHAR(50) NOT NULL, mail VARCHAR(255) NOT NULL, balance DOUBLE PRECISION DEFAULT \'0\', roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', locale VARCHAR(255) NOT NULL, theme VARCHAR(25) NOT NULL, UNIQUE INDEX UNIQ_8D93D6495126AC48 (mail), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA5BC2E0E FOREIGN KEY (trip_id) REFERENCES trip (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA5BC2E0E FOREIGN KEY (trip_id) REFERENCES trip (id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53BE4414630 FOREIGN KEY (from_c_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53B76398A1C FOREIGN KEY (to_c_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53BC3423909 FOREIGN KEY (driver_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE trip DROP FOREIGN KEY FK_7656F53BE4414630');
        $this->addSql('ALTER TABLE trip DROP FOREIGN KEY FK_7656F53B76398A1C');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEA5BC2E0E');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA5BC2E0E');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEA76ED395');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE trip DROP FOREIGN KEY FK_7656F53BC3423909');
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE trip');
        $this->addSql('DROP TABLE user');
    }
}
