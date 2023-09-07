<?php

namespace Mehedi8gb\ApiCrudify\Commands;

use Illuminate\Console\Command;

class ApiCrudifyCommand extends Command
{
    public $signature = 'api-crudify';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
