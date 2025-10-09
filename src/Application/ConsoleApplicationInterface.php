<?php

declare(strict_types=1);

namespace App\Application;

interface ConsoleApplicationInterface
{
    public function getApp(): ApplicationInterface;
}
