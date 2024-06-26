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
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description');
            $table->string('slug', 255)->unique();
            $table->string('author', 255);
            $table->string('image', 255);
            $table->integer('duration');
            $table->string('duration_string')->nullable();
            $table->string('mp3_path')->nullable();
            $table->string('published_at')->nullable();

            $table->timestamps();

            $table->foreign("album_id")->references('id')->on('albums')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
