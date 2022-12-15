<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221215133850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE about_carrousel_image DROP image');
        $this->addSql('ALTER TABLE article_carrousel_image DROP name');
        $this->addSql('ALTER TABLE carrousel_images DROP name');
        $this->addSql('ALTER TABLE product_carrousel_image DROP name');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_carrousel_image ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE carrousel_images ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE product_carrousel_image ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE about_carrousel_image ADD image VARCHAR(255) NOT NULL');
    }
}
