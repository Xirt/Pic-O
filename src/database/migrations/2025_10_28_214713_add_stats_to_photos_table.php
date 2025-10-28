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
        Schema::table('photos', function (Blueprint $table)
        {
            $table->unsignedBigInteger('impressions')->default(0)->after('size');
            $table->unsignedBigInteger('downloads')->default(0)->after('impressions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table)
        {
            $table->dropColumn(['impressions', 'downloads']);
        });
    }
};
