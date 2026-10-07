<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examples', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('text');
            $table->string('status')->default('draft')->index()->after('slug');
            $table->date('due_at')->nullable()->index()->after('status');
            $table->longText('content')->nullable()->after('textarea');
            $table->json('meta')->nullable()->after('content');
            $table->unsignedInteger('position')->default(0)->after('meta');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        $this->backfillSlugs();
    }

    /**
     * Give pre-existing rows a unique slug ("{text}-{id}").
     */
    public function backfillSlugs(): void
    {
        DB::table('examples')->whereNull('slug')->orderBy('id')->each(function (object $row): void {
            DB::table('examples')
                ->where('id', $row->id)
                ->update(['slug' => Str::slug((string) $row->text).'-'.$row->id]);
        });
    }

    public function down(): void
    {
        Schema::table('examples', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropIndex(['status']);
            $table->dropIndex(['due_at']);
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropSoftDeletes();
            $table->dropColumn(['slug', 'status', 'due_at', 'content', 'meta', 'position']);
        });
    }
};
