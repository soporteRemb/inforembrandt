<?php

namespace App\Services\Importacion\Acudientes;

use App\Models\Guardian;
use App\Models\Student;
use App\Services\Importacion\BaseImportService;
use App\Services\Importacion\Contracts\ImportadorInterface;
use App\Services\Importacion\DTO\ResultadoImportacion;
use Illuminate\Support\Facades\DB;

class AcudientesImportService extends BaseImportService implements ImportadorInterface
{
    public function importar(string $path, array $opciones = []): ResultadoImportacion
    {
        $resultado = $this->nuevoResultado();

        $sedeId = (int) ($opciones['sede_id'] ?? 0);
        $periodoLectivoId = (int) ($opciones['periodo_lectivo_id'] ?? 0);

        if (! $sedeId || ! $periodoLectivoId) {
            $resultado->agregarError('Debe seleccionar sede y periodo lectivo.');
            return $resultado;
        }

        $sheet = $this->abrirHojaActiva($path);
        $ultimaFila = $sheet->getHighestDataRow();

        DB::beginTransaction();

        try {
            for ($fila = 2; $fila <= $ultimaFila; $fila++) {
                $documentoEstudiante = $this->normalizarTexto($this->valor($sheet, "A{$fila}"));

                if ($documentoEstudiante === '') {
                    continue;
                }

                $resultado->filasLeidas++;

                $student = Student::query()
                    ->where('sede_id', $sedeId)
                    ->where('periodo_lectivo_id', $periodoLectivoId)
                    ->where('documento', $documentoEstudiante)
                    ->first();

                if (! $student) {
                    $this->agregarError($fila, "No existe estudiante con documento {$documentoEstudiante}.");
                    $resultado->omitidos++;
                    continue;
                }

                $bloques = [
                    'acudiente' => ['B', 'C', 'D', 'E', 'F'],
                    'padre' => ['G', 'H', 'I', 'J', 'K'],
                    'madre' => ['L', 'M', 'N', 'O', 'P'],
                    'deudor_economico' => ['Q', 'R', 'S', 'T', 'U'],
                ];

                foreach ($bloques as $tipo => $columnas) {
                    [$colNombre, $colDocumento, $colTelefono, $colCorreo, $colDireccion] = $columnas;

                    $nombre = $this->normalizarTexto($this->valor($sheet, "{$colNombre}{$fila}"));
                    $documento = $this->normalizarTexto($this->valor($sheet, "{$colDocumento}{$fila}"));
                    $telefono = $this->normalizarTexto($this->valor($sheet, "{$colTelefono}{$fila}"));
                    $correo = $this->normalizarTexto($this->valor($sheet, "{$colCorreo}{$fila}"));
                    $direccion = $this->normalizarTexto($this->valor($sheet, "{$colDireccion}{$fila}"));

                    if ($nombre === '' && $documento === '' && $telefono === '' && $correo === '' && $direccion === '') {
                        continue;
                    }

                    if ($nombre === '') {
                        $this->agregarError($fila, "El {$tipo} del estudiante {$documentoEstudiante} no tiene nombre.");
                        $resultado->omitidos++;
                        continue;
                    }

                    if ($documento === '') {
                        $documento = $this->documentoTemporal($student->documento, $tipo, $nombre);
                    }

                    $guardianExistente = Guardian::query()
                        ->where('documento', $documento)
                        ->first();

                    $guardian = Guardian::updateOrCreate(
                        [
                            'documento' => $documento,
                        ],
                        [
                            // Compatibilidad temporal con estructura anterior
                            'student_id' => $guardianExistente?->student_id ?? $student->id,
                            'tipo' => $guardianExistente?->tipo ?? $tipo,

                            'nombre' => $nombre,
                            'tipo_documento' => 'CC',
                            'telefono' => $telefono ?: null,
                            'correo' => $correo ?: null,
                            'direccion' => $direccion ?: null,
                            'estado' => 'activo',
                        ]
                    );

                    $relacionExiste = DB::table('guardian_student')
                        ->where('guardian_id', $guardian->id)
                        ->where('student_id', $student->id)
                        ->where('tipo', $tipo)
                        ->exists();

                    DB::table('guardian_student')->updateOrInsert(
                        [
                            'guardian_id' => $guardian->id,
                            'student_id' => $student->id,
                            'tipo' => $tipo,
                        ],
                        [
                            'estado' => 'activo',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    if ($relacionExiste) {
                        $resultado->actualizados++;
                    } else {
                        $resultado->creados++;
                    }
                }
            }

            DB::commit();

            return $resultado;
        } catch (\Throwable $e) {
            DB::rollBack();

            $resultado->agregarError('Error general durante la importación: ' . $e->getMessage());

            return $resultado;
        }
    }

    private function documentoTemporal(string $documentoEstudiante, string $tipo, string $nombre): string
    {
        return 'TEMP-' . strtoupper($tipo) . '-' . $documentoEstudiante . '-' . substr(md5($nombre), 0, 8);
    }
}