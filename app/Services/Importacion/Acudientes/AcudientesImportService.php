<?php

namespace App\Services\Importacion\Acudientes;

use App\Models\Guardian;
use App\Models\Student;
use App\Services\Importacion\BaseImportService;
use App\Services\Importacion\Contracts\ImportadorInterface;
use App\Services\Importacion\DTO\ResultadoImportacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AcudientesImportService extends BaseImportService implements ImportadorInterface
{
    public function importar(
        string $path,
        array $opciones = []
    ): ResultadoImportacion {
        $resultado = $this->nuevoResultado();

        $sedeId = (int) ($opciones['sede_id'] ?? 0);
        $periodoLectivoId = (int) (
            $opciones['periodo_lectivo_id'] ?? 0
        );

        if (! $sedeId || ! $periodoLectivoId) {
            $resultado->agregarError(
                'Debe seleccionar sede y periodo lectivo.'
            );

            return $resultado;
        }

        $sheet = $this->abrirHojaActiva($path);
        $ultimaFila = $sheet->getHighestDataRow();

        /*
        |--------------------------------------------------------------------------
        | Detectar plantilla ampliada
        |--------------------------------------------------------------------------
        |
        | El formato histórico termina en U.
        | La plantilla generada desde Pre-matrículas agrega información desde V.
        |
        */

        $encabezadoTipoDocumentoAcudiente = mb_strtoupper(
            trim((string) $sheet->getCell('V1')->getValue()),
            'UTF-8'
        );

        $esArchivoPreMatricula =
            $encabezadoTipoDocumentoAcudiente
            === 'TIPO_DOCUMENTO_ACUDIENTE';

        /*
        |--------------------------------------------------------------------------
        | Compatibilidad con la estructura de guardians
        |--------------------------------------------------------------------------
        |
        | Solo se enviarán estos datos si las columnas existen realmente.
        |
        */

        $tieneLugarTrabajo = Schema::hasColumn(
            'guardians',
            'lugar_trabajo'
        );

        $tieneParentesco = Schema::hasColumn(
            'guardians',
            'parentesco'
        );

        DB::beginTransaction();

        try {
            for ($fila = 2; $fila <= $ultimaFila; $fila++) {
                /*
                |--------------------------------------------------------------------------
                | Documento del estudiante
                |--------------------------------------------------------------------------
                */

                $documentoEstudiante = $this->normalizarTexto(
                    $this->valor($sheet, "A{$fila}")
                );

                if ($documentoEstudiante === '') {
                    continue;
                }

                $resultado->filasLeidas++;

                $student = Student::query()
                    ->where('sede_id', $sedeId)
                    ->where(
                        'periodo_lectivo_id',
                        $periodoLectivoId
                    )
                    ->where(
                        'documento',
                        $documentoEstudiante
                    )
                    ->first();

                if (! $student) {
                    $this->agregarError(
                        $fila,
                        "No existe estudiante con documento {$documentoEstudiante}."
                    );

                    $resultado->omitidos++;

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Bloques históricos A-U
                |--------------------------------------------------------------------------
                |
                | Cada bloque contiene:
                | nombre, documento, teléfono, correo y dirección.
                |
                */

                $bloques = [
                    'acudiente' => [
                        'columnas' => ['B', 'C', 'D', 'E', 'F'],
                        'tipo_documento' => 'V',
                        'lugar_trabajo' => 'W',
                        'parentesco' => 'X',
                    ],

                    'padre' => [
                        'columnas' => ['G', 'H', 'I', 'J', 'K'],
                        'tipo_documento' => 'Y',
                        'lugar_trabajo' => 'Z',
                        'parentesco' => null,
                    ],

                    'madre' => [
                        'columnas' => ['L', 'M', 'N', 'O', 'P'],
                        'tipo_documento' => 'AA',
                        'lugar_trabajo' => 'AB',
                        'parentesco' => null,
                    ],

                    'deudor_economico' => [
                        'columnas' => ['Q', 'R', 'S', 'T', 'U'],
                        'tipo_documento' => null,
                        'lugar_trabajo' => null,
                        'parentesco' => null,
                    ],
                ];

                foreach ($bloques as $tipo => $configuracion) {
                    [
                        $colNombre,
                        $colDocumento,
                        $colTelefono,
                        $colCorreo,
                        $colDireccion,
                    ] = $configuracion['columnas'];

                    /*
                    |--------------------------------------------------------------------------
                    | Datos del formato histórico
                    |--------------------------------------------------------------------------
                    */

                    $nombre = $this->normalizarTexto(
                        $this->valor(
                            $sheet,
                            "{$colNombre}{$fila}"
                        )
                    );

                    $documento = $this->normalizarTexto(
                        $this->valor(
                            $sheet,
                            "{$colDocumento}{$fila}"
                        )
                    );

                    $telefono = $this->normalizarTexto(
                        $this->valor(
                            $sheet,
                            "{$colTelefono}{$fila}"
                        )
                    );

                    $correo = $this->normalizarTexto(
                        $this->valor(
                            $sheet,
                            "{$colCorreo}{$fila}"
                        )
                    );

                    $direccion = $this->normalizarTexto(
                        $this->valor(
                            $sheet,
                            "{$colDireccion}{$fila}"
                        )
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Datos adicionales de la plantilla ampliada
                    |--------------------------------------------------------------------------
                    */

                    $tipoDocumentoExcel = '';

                    if (
                        $esArchivoPreMatricula
                        && filled(
                            $configuracion['tipo_documento']
                        )
                    ) {
                        $tipoDocumentoExcel =
                            $this->normalizarTexto(
                                $this->valor(
                                    $sheet,
                                    $configuracion['tipo_documento']
                                    . $fila
                                )
                            );
                    }

                    $lugarTrabajo = '';

                    if (
                        $esArchivoPreMatricula
                        && filled(
                            $configuracion['lugar_trabajo']
                        )
                    ) {
                        $lugarTrabajo =
                            $this->normalizarTexto(
                                $this->valor(
                                    $sheet,
                                    $configuracion['lugar_trabajo']
                                    . $fila
                                )
                            );
                    }

                    $parentesco = '';

                    if (
                        $esArchivoPreMatricula
                        && filled(
                            $configuracion['parentesco']
                        )
                    ) {
                        $parentesco =
                            $this->normalizarTexto(
                                $this->valor(
                                    $sheet,
                                    $configuracion['parentesco']
                                    . $fila
                                )
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Ignorar bloques completamente vacíos
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $nombre === ''
                        && $documento === ''
                        && $telefono === ''
                        && $correo === ''
                        && $direccion === ''
                    ) {
                        continue;
                    }

                    if ($nombre === '') {
                        $tipoVisible = $this->tipoVisible(
                            $tipo
                        );

                        $this->agregarError(
                            $fila,
                            "El {$tipoVisible} del estudiante {$documentoEstudiante} no tiene nombre."
                        );

                        $resultado->omitidos++;

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Documento temporal
                    |--------------------------------------------------------------------------
                    |
                    | Se conserva el comportamiento actual para responsables sin
                    | número de documento.
                    |
                    */

                    if ($documento === '') {
                        $documento =
                            $this->documentoTemporal(
                                $student->documento,
                                $tipo,
                                $nombre
                            );
                    }

                    $guardianExistente = Guardian::query()
                        ->where('documento', $documento)
                        ->first();

                    $tipoDocumento =
                        $tipoDocumentoExcel !== ''
                            ? $this->normalizarTipoDocumento(
                                $tipoDocumentoExcel
                            )
                            : (
                                $guardianExistente?->tipo_documento
                                ?: 'CC'
                            );

                    /*
                    |--------------------------------------------------------------------------
                    | Datos para crear o actualizar Guardian
                    |--------------------------------------------------------------------------
                    */

                    $datosGuardian = [
                        /*
                        | Compatibilidad temporal con la estructura anterior.
                        */
                        'student_id' =>
                            $guardianExistente?->student_id
                            ?? $student->id,

                        'tipo' =>
                            $guardianExistente?->tipo
                            ?? $tipo,

                        'nombre' => $nombre,
                        'tipo_documento' => $tipoDocumento,
                        'telefono' => $telefono ?: null,
                        'correo' => $correo ?: null,
                        'direccion' => $direccion ?: null,
                        'estado' => 'activo',
                    ];

                    /*
                    |--------------------------------------------------------------------------
                    | Lugar de trabajo
                    |--------------------------------------------------------------------------
                    |
                    | El formato antiguo no trae este dato. Si se actualiza un
                    | acudiente existente con un archivo antiguo, conservamos el
                    | valor almacenado.
                    |
                    */

                    if ($tieneLugarTrabajo) {
                        $datosGuardian['lugar_trabajo'] =
                            $lugarTrabajo !== ''
                                ? $lugarTrabajo
                                : (
                                    $guardianExistente?->lugar_trabajo
                                    ?: null
                                );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Parentesco
                    |--------------------------------------------------------------------------
                    |
                    | Solo la sección principal de acudiente trae parentesco en X.
                    |
                    */

                    if ($tieneParentesco) {
                        $datosGuardian['parentesco'] =
                            $parentesco !== ''
                                ? $parentesco
                                : (
                                    $guardianExistente?->parentesco
                                    ?: $this->parentescoPredeterminado(
                                        $tipo
                                    )
                                );
                    }

                    $guardian = Guardian::updateOrCreate(
                        [
                            'documento' => $documento,
                        ],
                        $datosGuardian
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Relación Guardian - Student
                    |--------------------------------------------------------------------------
                    */

                    $relacionExiste = DB::table(
                        'guardian_student'
                    )
                        ->where(
                            'guardian_id',
                            $guardian->id
                        )
                        ->where(
                            'student_id',
                            $student->id
                        )
                        ->where('tipo', $tipo)
                        ->exists();

                    DB::table('guardian_student')
                        ->updateOrInsert(
                            [
                                'guardian_id' =>
                                    $guardian->id,

                                'student_id' =>
                                    $student->id,

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

            $resultado->agregarError(
                'Error general durante la importación: '
                . $e->getMessage()
            );

            return $resultado;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Documento temporal
    |--------------------------------------------------------------------------
    */

    private function documentoTemporal(
        string $documentoEstudiante,
        string $tipo,
        string $nombre
    ): string {
        return 'TEMP-'
            . strtoupper($tipo)
            . '-'
            . $documentoEstudiante
            . '-'
            . substr(md5($nombre), 0, 8);
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar tipo de documento
    |--------------------------------------------------------------------------
    */

    private function normalizarTipoDocumento(
        string $tipoDocumento
    ): string {
        return match (
            mb_strtoupper(
                trim($tipoDocumento),
                'UTF-8'
            )
        ) {
            'RC',
            'REGISTRO CIVIL' => 'RC',

            'TI',
            'TARJETA DE IDENTIDAD' => 'TI',

            'CC',
            'CÉDULA DE CIUDADANÍA',
            'CEDULA DE CIUDADANIA' => 'CC',

            'CE',
            'CÉDULA DE EXTRANJERÍA',
            'CEDULA DE EXTRANJERIA' => 'CE',

            'PASAPORTE',
            'PA' => 'PA',

            '',
            'SIN DEFINIR' => 'CC',

            default => mb_strtoupper(
                trim($tipoDocumento),
                'UTF-8'
            ),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Parentesco predeterminado
    |--------------------------------------------------------------------------
    */

    private function parentescoPredeterminado(
        string $tipo
    ): ?string {
        return match ($tipo) {
            'padre' => 'Padre',
            'madre' => 'Madre',
            'deudor_economico' => 'Deudor económico',
            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Nombre visible del tipo
    |--------------------------------------------------------------------------
    */

    private function tipoVisible(string $tipo): string
    {
        return match ($tipo) {
            'deudor_economico' => 'deudor económico',
            default => str_replace('_', ' ', $tipo),
        };
    }
}