<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        // 2. Eliminar primero la foreign key de student_id
        DB::statement('
            ALTER TABLE student_documentos
            DROP FOREIGN KEY student_documentos_student_id_foreign
        ');

        // 3. Eliminar el UNIQUE que dependía de student_id
        DB::statement('
            ALTER TABLE student_documentos
            DROP INDEX student_documentos_student_id_tipo_unique
        ');

        // 4. Eliminar student_id
        DB::statement('
            ALTER TABLE student_documentos
            DROP COLUMN student_id
        ');

        // 5. student_codigo pasa a ser obligatorio
        DB::statement('
            ALTER TABLE student_documentos
            MODIFY student_codigo VARCHAR(255) NOT NULL
        ');

        // 6. Nuevo UNIQUE por código de estudiante + tipo
        DB::statement('
            ALTER TABLE student_documentos
            ADD UNIQUE KEY student_documentos_student_codigo_tipo_unique
            (student_codigo, tipo)
        ');
    }

    public function down(): void
    {
        // 1. Retirar el índice nuevo
        DB::statement('
            ALTER TABLE student_documentos
            DROP INDEX student_documentos_student_codigo_tipo_unique
        ');

        // 2. Volver student_codigo nullable temporalmente
        DB::statement('
            ALTER TABLE student_documentos
            MODIFY student_codigo VARCHAR(255) NULL
        ');

        // 3. Recuperar student_id
        Schema::table('student_documentos', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->after('id');
        });

        // 4. Reconstruir student_id desde student_codigo
        DB::statement('
            UPDATE student_documentos sd
            JOIN students s ON s.codigo = sd.student_codigo
            SET sd.student_id = s.id
            WHERE sd.student_id IS NULL
        ');

        // 5. Restaurar estructura original
        DB::statement('
            ALTER TABLE student_documentos
            MODIFY student_id BIGINT UNSIGNED NOT NULL
        ');

        DB::statement('
            ALTER TABLE student_documentos
            ADD UNIQUE KEY student_documentos_student_id_tipo_unique
            (student_id, tipo)
        ');

        DB::statement('
            ALTER TABLE student_documentos
            ADD CONSTRAINT student_documentos_student_id_foreign
            FOREIGN KEY (student_id)
            REFERENCES students(id)
            ON DELETE CASCADE
        ');
    }
};