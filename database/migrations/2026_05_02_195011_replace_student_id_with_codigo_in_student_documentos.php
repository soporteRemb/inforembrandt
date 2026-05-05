<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rellenar student_codigo con el código real del estudiante
        DB::statement('
            UPDATE student_documentos sd
            JOIN students s ON s.id = sd.student_id
            SET sd.student_codigo = s.codigo
            WHERE sd.student_codigo IS NULL
        ');

        // 2. Eliminar el UNIQUE KEY que bloquea el drop de student_id
        DB::statement('ALTER TABLE student_documentos DROP INDEX student_documentos_student_id_tipo_unique');

        // 3. Eliminar columna student_id
        DB::statement('ALTER TABLE student_documentos DROP COLUMN student_id');

        // 4. Hacer student_codigo NOT NULL y crear nuevo UNIQUE KEY (student_codigo, tipo)
        DB::statement('ALTER TABLE student_documentos MODIFY student_codigo VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE student_documentos ADD UNIQUE KEY student_documentos_student_codigo_tipo_unique (student_codigo, tipo)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE student_documentos DROP INDEX student_documentos_student_codigo_tipo_unique');
        DB::statement('ALTER TABLE student_documentos MODIFY student_codigo VARCHAR(255) NULL');
        Schema::table('student_documentos', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->after('id');
            $table->unique(['student_id', 'tipo']);
        });
    }
};
