<?php

declare(strict_types=1);

namespace App\Application\Modules\System\Console\Commands;

use Symfony\Component\Console\Command\Command;

interface CommandInterface
{
    public function getCommand(): Command;
}
