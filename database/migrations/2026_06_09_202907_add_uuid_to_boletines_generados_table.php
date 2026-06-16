<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boletines_generados', function (Blueprint $table) {
            $table->uuid('uuid')
                ->unique()
                ->after('id')
                ->nullable();
        });

        DB::table('boletines_generados')->get()->each(function ($boletin) {
            DB::table('boletines_generados')
                ->where('id', $boletin->id)
                ->update([
                    'uuid' => Str::uuid(),
                ]);
        });

        Schema::table('boletines_generados', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('boletines_generados', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};