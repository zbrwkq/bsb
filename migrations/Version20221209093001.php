<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221209093001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contenu_panier_panier DROP FOREIGN KEY FK_73141215F77D927C');
        $this->addSql('ALTER TABLE contenu_panier_panier DROP FOREIGN KEY FK_7314121561405BF');
        $this->addSql('DROP TABLE contenu_panier_panier');
        $this->addSql('ALTER TABLE contenu_panier ADD panier_id INT NOT NULL');
        $this->addSql('ALTER TABLE contenu_panier ADD CONSTRAINT FK_80507DC0F77D927C FOREIGN KEY (panier_id) REFERENCES panier (id)');
        $this->addSql('CREATE INDEX IDX_80507DC0F77D927C ON contenu_panier (panier_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contenu_panier_panier (contenu_panier_id INT NOT NULL, panier_id INT NOT NULL, INDEX IDX_7314121561405BF (contenu_panier_id), INDEX IDX_73141215F77D927C (panier_id), PRIMARY KEY(contenu_panier_id, panier_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE contenu_panier_panier ADD CONSTRAINT FK_73141215F77D927C FOREIGN KEY (panier_id) REFERENCES panier (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contenu_panier_panier ADD CONSTRAINT FK_7314121561405BF FOREIGN KEY (contenu_panier_id) REFERENCES contenu_panier (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contenu_panier DROP FOREIGN KEY FK_80507DC0F77D927C');
        $this->addSql('DROP INDEX IDX_80507DC0F77D927C ON contenu_panier');
        $this->addSql('ALTER TABLE contenu_panier DROP panier_id');
    }
}
