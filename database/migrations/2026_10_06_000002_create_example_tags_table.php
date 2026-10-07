<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('example_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 7)->nullable();
            $table->timestamps();
        });

        Schema::create('example_example_tag', function (Blueprint $table) {
            $table->foreignId('example_id')->constrained('examples')->cascadeOnDelete();
            $table->foreignId('example_tag_id')->constrained('example_tags')->cascadeOnDelete();
            $table->primary(['example_id', 'example_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('example_example_tag');
        Schema::dropIfExists('example_tags');
    }
};
