<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Suite;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220330092908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create table for ' . Suite::class;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE suite (
                id VARCHAR(32) NOT NULL, 
                user_id VARCHAR(32) NOT NULL, 
                source_id VARCHAR(32) NOT NULL, 
                label VARCHAR(255) NOT NULL, 
                tests TEXT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('COMMENT ON COLUMN suite.tests IS \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE suite');
    }
}
