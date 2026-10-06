<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('environment_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('environment_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            // Holds ciphertext (see the `encrypted` cast), so it must be text and may be null when empty.
            $table->text('value')->nullable();
            $table->boolean('is_secret')->default(false);
            $table->timestamps();

            $table->unique(['environment_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environment_variables');
    }
};
