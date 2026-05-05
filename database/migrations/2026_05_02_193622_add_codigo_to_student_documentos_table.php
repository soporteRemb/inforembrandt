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
        Schema::table('student_documentos', function (Blueprint $table) {
            $table->string('student_codigo')->nullable()->after('student_id');
        });
    }

    public function down(): void
    {
        Schema::table('student_documentos', function (Blueprint $table) {
            $table->dropColumn('student_codigo');
        });
    }
};
