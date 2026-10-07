<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('example_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('example_id')->constrained('examples')->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 127);
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['example_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('example_attachments');
    }
};
