<?php

declare(strict_types=1);

namespace App\Infrastructure\Common\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration inicial - Criação da tabela users
 */
final class Version20250924165515 extends AbstractMigration
{
    public function down(Schema $schema): void
    {
        // Remover a tabela users
        $schema->dropTable('users');
    }

    public function getDescription(): string
    {
        return 'Criação da tabela users com todos os campos necessários baseada na UserEntity';
    }

    public function up(Schema $schema): void
    {
        // Criação da tabela users
        $table = $schema->createTable('users');

        // Campos principais
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('password', 'string', ['length' => 255]);
        $table->addColumn('role', 'string', ['length' => 50, 'default' => 'user']);
        $table->addColumn('status', 'string', ['length' => 20, 'default' => 'active']);

        // Campos de controle
        $table->addColumn('uuid', 'string', ['length' => 36, 'notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');

        // Chaves e índices
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email'], 'unique_users_email');
        $table->addIndex(['status'], 'idx_users_status');
        $table->addIndex(['role'], 'idx_users_role');
        $table->addIndex(['uuid'], 'idx_users_uuid');
    }
}
