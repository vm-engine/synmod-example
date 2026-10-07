<?php

declare(strict_types=1);

namespace VmEngine\Example\Console;

use Illuminate\Console\Command;
use VmEngine\Example\Support\ExampleSearchIndex;

class SearchReindexCommand extends Command
{
    protected $signature = 'example:search-reindex';

    protected $description = 'Rebuild the example entries in the global search index';

    public function handle(): int
    {
        if (! synsearchable()->isEnabled()) {
            $this->warn('  Global search is not set up — run `php artisan synapps:searchable-setup` first.');

            return self::SUCCESS;
        }

        $this->line('  '.ExampleSearchIndex::reindex().' examples indexed');

        return self::SUCCESS;
    }
}
