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
        Schema::create('photos', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Folder relationship
            $table->foreignId('folder_id')
                  ->constrained('folders')
                  ->onDelete('cascade');

            // Basic file info
            $table->string('filename');
            $table->string('blurhash')->nullable();
			$table->integer('width')->nullable();
			$table->integer('height')->nullable();
            $table->bigInteger('size')->nullable();

            // EXIF metadata
            $table->string('camera')->nullable();
            $table->string('make')->nullable();
            $table->string('orientation')->nullable();
            $table->string('aperture')->nullable();
            $table->string('iso')->nullable();
            $table->string('shutter_speed')->nullable();
            $table->string('exposure_time')->nullable();
            $table->string('focal_length')->nullable();
            $table->timestamp('taken_at')->nullable();

            // Prevent duplicates within a folder
            $table->unique(['folder_id', 'filename']);

        });  			
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
