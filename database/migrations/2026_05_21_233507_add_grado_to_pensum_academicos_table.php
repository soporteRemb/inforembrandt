<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pensum_academicos', function (Blueprint $table) {

            if (! Schema::hasColumn('pensum_academicos', 'grado')) {
                $table->integer('grado')
                    ->nullable()
                    ->after('course_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pensum_academicos', function (Blueprint $table) {

            if (Schema::hasColumn('pensum_academicos', 'grado')) {
                $table->dropColumn('grado');
            }
        });
    }
};