<?php

declare(strict_types=1);

namespace App\Infrastructure\Common\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251008192239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria bank_accounts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE bank_accounts (
              id SERIAL PRIMARY KEY,
              bank VARCHAR(100) NOT NULL,
              agency VARCHAR(10) NOT NULL,
              account VARCHAR(20) NOT NULL,
              currentBalance NUMERIC(15,2) NOT NULL,
              active BOOLEAN NOT NULL,
              created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
            )"
        );

        $this->addSql("CREATE INDEX idx_bank_account_bank   ON bank_accounts (bank)");
        $this->addSql("CREATE INDEX idx_bank_account_agency ON bank_accounts (agency)");
        $this->addSql("CREATE INDEX idx_bank_account_account ON bank_accounts (account)");

        $this->addSql("CREATE UNIQUE INDEX uniq_bank_agency_account ON bank_accounts (bank, agency, account)");

        $this->addSql("CREATE TYPE account_type_enum AS ENUM ('corrente','poupanca','salario')");
        $this->addSql("ALTER TABLE bank_accounts ADD COLUMN account_type account_type_enum NOT NULL DEFAULT 'corrente'");
        $this->addSql("ALTER TABLE bank_accounts ALTER COLUMN account_type DROP DEFAULT");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE IF EXISTS bank_accounts");
        $this->addSql("DROP TYPE IF EXISTS account_type_enum");
    }
}
