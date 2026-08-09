<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pre_matriculas', function (Blueprint $table) {
            $table->string('eps_otro', 180)
                ->nullable()
                ->after('eps_id');
        });
    }

    public function down(): void
    {
        Schema::table('pre_matriculas', function (Blueprint $table) {
            $table->dropColumn('eps_otro');
        });
    }
};