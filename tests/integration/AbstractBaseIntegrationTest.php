<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Application\ApplicationInterface;
use App\Infrastructure\Common\Database\DoctrineEntityManagerInterface;
use Doctrine\DBAL\Connection;

abstract class AbstractBaseIntegrationTest extends TestCase
{
    protected ApplicationInterface $app;
    protected DoctrineEntityManagerInterface $doctrineManager;
    protected Connection $connection;

    protected function setUp(): void
    {
        $this->app = \App\Application\Impl\ApiApplication::getInstance();
        $this->doctrineManager = $this->app->container()->get(DoctrineEntityManagerInterface::class);
        $this->connection = $this->doctrineManager->getMaster()->getConnection();
        
        $this->setUpDatabase();
        $this->cleanDatabase();
        
        // Iniciar transação para isolar cada teste
        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback da transação para limpar dados do teste
        if ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }
    }

    private function setUpDatabase(): void
    {
        // Create users table if it doesn't exist
        $this->connection->executeStatement("
            CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50) NOT NULL DEFAULT 'user',
                status VARCHAR(20) NOT NULL DEFAULT 'active',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                uuid VARCHAR(36) NULL
            )
        ");
    }
    
    private function cleanDatabase(): void
    {
        // Limpar dados órfãos antes de cada teste
        try {
            $this->connection->executeStatement("DELETE FROM users");
        } catch (\Exception $e) {
            // Se a tabela não existir, ignorar o erro
        }
    }

    protected function createTestUser(string $name, string $email, string $password, string $role = 'user'): int
    {
        $this->connection->insert('users', [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'uuid' => uniqid('test_', true)
        ]);

        return $this->connection->lastInsertId();
    }
}