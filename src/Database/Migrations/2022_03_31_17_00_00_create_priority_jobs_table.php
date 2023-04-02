<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('priority_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->integer('priority')->index();
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at')->index();
            $table->unsignedInteger('created_at')->index();

            $table->rawIndex(
                'priority DESC, created_at ASC',
                'idx_priority_sort'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('priority_jobs');
    }
};
