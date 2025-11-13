<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('examples', function (Blueprint $table) {
            $table->id();
            $table->string('text');
            $table->string('email');
            $table->string('protected');
            $table->unsignedInteger('number');
            $table->unsignedInteger('masked')->nullable();
            $table->text('textarea')->nullable();
            $table->unsignedInteger('dropdown');
            $table->json('multidropdown')->nullable();
            $table->unsignedInteger('radio')->nullable();
            $table->json('checkbox')->nullable();
            $table->date('date')->nullable();
            $table->timestamp('datetime')->nullable();
            $table->string('file')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examples');
    }
};
