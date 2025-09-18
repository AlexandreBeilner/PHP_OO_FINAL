<?php

declare(strict_types=1);

namespace App\Common\Database;

interface DoctrineEntityManagerFactoryInterface
{
    public static function create(array $connectionConfig): DoctrineEntityManagerInterface;
}
