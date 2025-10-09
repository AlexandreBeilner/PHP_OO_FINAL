<?php

declare(strict_types=1);

namespace App\Infrastructure\Common\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration para criação da tabela products
 */
final class Version20251001120000 extends AbstractMigration
{
    public function down(Schema $schema): void
    {
        // Remover a tabela products
        $schema->dropTable('products');
    }

    public function getDescription(): string
    {
        return 'Criação da tabela products com todos os campos necessários baseada na ProductEntity';
    }

    public function up(Schema $schema): void
    {
        // Criação da tabela products
        $table = $schema->createTable('products');

        // Campos principais
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2]);
        $table->addColumn('category', 'string', ['length' => 100]);
        $table->addColumn('status', 'string', ['length' => 20, 'default' => 'draft']);

        // Campos de controle
        $table->addColumn('uuid', 'string', ['length' => 36, 'notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');

        // Chaves e índices
        $table->setPrimaryKey(['id']);
        $table->addIndex(['status'], 'idx_products_status');
        $table->addIndex(['category'], 'idx_products_category');
        $table->addIndex(['price'], 'idx_products_price');
        $table->addIndex(['uuid'], 'idx_products_uuid');
        $table->addIndex(['name'], 'idx_products_name');
    }
}
