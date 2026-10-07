<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * User field extension demo: a per-user default example category, shown on the
 * backend user form via SynAuthExtension (see ExampleServiceProvider).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'example_default_category_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('example_default_category_id')->nullable()
                ->constrained('example_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'example_default_category_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('example_default_category_id');
        });
    }
};
