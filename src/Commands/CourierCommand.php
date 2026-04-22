<?php

declare(strict_types = 1);

namespace Centrex\Courier\Commands;

use Illuminate\Console\Command;

class CourierCommand extends Command
{
    public $signature = 'courier';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
