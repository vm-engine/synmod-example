<?php

declare(strict_types=1);

namespace VmEngine\Example\Console;

use Illuminate\Console\Command;
use VmEngine\Example\Support\ExampleAuthorRole;
use VmEngine\Example\Support\ExampleSearchIndex;

/**
 * Wires the module's admin JS (rich-text editor) into the host's
 * resources/js/app.js and ensures the Example author demo role. Idempotent.
 */
class SetupCommand extends Command
{
    protected $signature = 'example:setup';

    protected $description = 'Add the example module JS to resources/js/app.js and ensure the Example author demo role';

    private const IMPORT = "import '../../vendor/vm-engine/synmod-example/resources/js/example.js';";

    private const MARKER = 'synmod-example/resources/js/example.js';

    public function handle(): int
    {
        ExampleAuthorRole::ensure();
        $this->line('  roles — Example author role ensured');
        $this->line('  search — '.ExampleSearchIndex::reindex().' examples indexed');

        $path = base_path('resources/js/app.js');

        if (! is_file($path)) {
            $this->warn('  resources/js/app.js not found — add manually: '.self::IMPORT);

            return self::SUCCESS;
        }

        $contents = (string) file_get_contents($path);

        if (str_contains($contents, self::MARKER)) {
            $this->line('  app.js — already patched (skipped)');
        } else {
            file_put_contents($path, rtrim($contents).PHP_EOL.self::IMPORT.PHP_EOL);
            $this->line('  app.js — example JS import added');
        }

        $this->line('  Next: run `npm install` and `npm run build`.');

        return self::SUCCESS;
    }
}
