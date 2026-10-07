<?php

declare(strict_types=1);

namespace VmEngine\Example\Console;

use Illuminate\Console\Command;

/**
 * Wires the module's admin JS (rich-text editor) into the host's
 * resources/js/app.js. Idempotent.
 */
class SetupCommand extends Command
{
    protected $signature = 'example:setup';

    protected $description = 'Add the example module JS (rich-text editor) to resources/js/app.js';

    private const IMPORT = "import '../../vendor/vm-engine/synmod-example/resources/js/example.js';";

    private const MARKER = 'synmod-example/resources/js/example.js';

    public function handle(): int
    {
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
