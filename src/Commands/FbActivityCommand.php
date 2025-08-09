<?php

namespace Mortezamasumi\FbActivity\Commands;

use Illuminate\Console\Command;

class FbActivityCommand extends Command
{
    public $signature = 'fb-activity';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
