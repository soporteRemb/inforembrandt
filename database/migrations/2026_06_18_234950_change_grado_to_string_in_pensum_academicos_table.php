<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pensum_academicos MODIFY grado VARCHAR(10) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pensum_academicos MODIFY grado INT NULL");
    }
};