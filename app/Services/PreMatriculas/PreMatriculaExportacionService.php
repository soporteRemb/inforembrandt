<?php

namespace App\Services\PreMatriculas;

use App\Models\PreMatricula;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PreMatriculaExportacionService
{
    /*
    |--------------------------------------------------------------------------
    | Exportar estudiantes
    |--------------------------------------------------------------------------
    |
    | Conserva A-L para compatibilidad con el formato histórico.
    | Los datos adicionales de la pre-matrícula comienzan en M.
    |
    */

    public function exportarEstudiantes(
        int $sedeId,
        int $periodoLectivoId
    ): BinaryFileResponse {
        $formularios = $this->obtenerFormulariosCompletados(
            $sedeId,
            $periodoLectivoId
        );

        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();
        $hoja->setTitle('ESTUDIANTES');

        $encabezados = [
            // Formato histórico A-L
            'A' => 'NOMBRES',
            'B' => 'APELLIDOS',
            'C' => 'IDENTIFICACION',
            'D' => 'FECHA_NACIMIENTO',
            'E' => 'GENERO',
            'F' => 'TELEFONO',
            'G' => 'EMAIL',
            'H' => 'DIRECCION',
            'I' => 'NIVEL',
            'J' => 'GRADO',
            'K' => 'SECCION',
            'L' => 'CODIGO',

            // Nuevas columnas M-V
            'M' => 'TIPO_DOCUMENTO',
            'N' => 'CIUDAD_EXPEDICION',
            'O' => 'CIUDAD_NACIMIENTO',
            'P' => 'RH',
            'Q' => 'EPS',
            'R' => 'TELEFONO_EMERGENCIA',
            'S' => 'NUMERO_HERMANOS',
            'T' => 'CONDICION_INGRESO',
            'U' => 'INSTITUCION_ANTERIOR',
            'V' => 'NUMERO_FORMULARIO',
        ];

        $this->escribirEncabezados(
            $hoja,
            $encabezados
        );

        $fila = 2;

        foreach ($formularios as $formulario) {
            $hoja->setCellValue(
                "A{$fila}",
                $this->texto($formulario->nombres)
            );

            $hoja->setCellValue(
                "B{$fila}",
                $this->texto($formulario->apellidos)
            );

            $this->escribirTexto(
                $hoja,
                "C{$fila}",
                $formulario->documento
            );

            $hoja->setCellValue(
                "D{$fila}",
                $formulario->fecha_nacimiento
                    ? $formulario->fecha_nacimiento->format('d/m/Y')
                    : ''
            );

            $hoja->setCellValue(
                "E{$fila}",
                $this->generoVisible($formulario->genero)
            );

            $this->escribirTexto(
                $hoja,
                "F{$fila}",
                $formulario->telefono
            );

            $hoja->setCellValue(
                "G{$fila}",
                $this->texto($formulario->correo)
            );

            $hoja->setCellValue(
                "H{$fila}",
                $this->texto($formulario->direccion)
            );

            /*
            |--------------------------------------------------------------------------
            | Columnas de matrícula que todavía no existen en pre-matrícula
            |--------------------------------------------------------------------------
            |
            | NIVEL, SECCIÓN y CÓDIGO quedan vacíos.
            | GRADO conserva el grado al que aspira.
            |
            */

            $hoja->setCellValue("I{$fila}", '');

            $hoja->setCellValue(
                "J{$fila}",
                $this->texto($formulario->grado_aspira)
            );

            $hoja->setCellValue("K{$fila}", '');
            $hoja->setCellValue("L{$fila}", '');

            /*
            |--------------------------------------------------------------------------
            | Información adicional de la pre-matrícula
            |--------------------------------------------------------------------------
            */

            $hoja->setCellValue(
                "M{$fila}",
                $this->texto($formulario->tipo_documento)
            );

            $hoja->setCellValue(
                "N{$fila}",
                $this->texto($formulario->ciudad_expedicion)
            );

            $hoja->setCellValue(
                "O{$fila}",
                $this->texto($formulario->ciudad_nacimiento)
            );

            $hoja->setCellValue(
                "P{$fila}",
                $this->texto($formulario->rh)
            );

            $nombreEps = $this->texto(
                $formulario->eps?->nombre
            );

            if (
                mb_strtolower(
                    $nombreEps,
                    'UTF-8'
                ) === 'otro'
            ) {
                $epsExportada = filled($formulario->eps_otro)
                    ? $this->texto($formulario->eps_otro)
                    : $nombreEps;
            } else {
                $epsExportada = $nombreEps;
            }

            $hoja->setCellValue(
                "Q{$fila}",
                $epsExportada
            );

            $this->escribirTexto(
                $hoja,
                "R{$fila}",
                $formulario->telefono_emergencia
            );

            $hoja->setCellValue(
                "S{$fila}",
                $formulario->numero_hermanos ?? 0
            );

            $hoja->setCellValue(
                "T{$fila}",
                $this->texto($formulario->condicion_ingreso)
            );

            $hoja->setCellValue(
                "U{$fila}",
                $this->texto($formulario->institucion_anterior)
            );

            $this->escribirTexto(
                $hoja,
                "V{$fila}",
                $formulario->numero_formulario
            );

            $fila++;
        }

        $this->aplicarPresentacion(
            $hoja,
            'A',
            'V',
            max(2, $fila - 1),
            [
                'A' => 25,
                'B' => 25,
                'C' => 18,
                'D' => 18,
                'E' => 14,
                'F' => 18,
                'G' => 30,
                'H' => 35,
                'I' => 14,
                'J' => 18,
                'K' => 14,
                'L' => 16,
                'M' => 22,
                'N' => 22,
                'O' => 22,
                'P' => 10,
                'Q' => 24,
                'R' => 22,
                'S' => 20,
                'T' => 20,
                'U' => 32,
                'V' => 24,
            ]
        );

        return $this->descargar(
            $spreadsheet,
            'estudiantes_pre_matricula'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Exportar acudientes
    |--------------------------------------------------------------------------
    |
    | Conserva A-U:
    | A: identificación del estudiante.
    | B-F: acudiente.
    | G-K: padre.
    | L-P: madre.
    | Q-U: deudor económico.
    |
    | El deudor económico queda vacío porque se diligencia manualmente durante
    | el proceso posterior de matrícula.
    |
    */

    public function exportarAcudientes(
        int $sedeId,
        int $periodoLectivoId
    ): BinaryFileResponse {
        $formularios = $this->obtenerFormulariosCompletados(
            $sedeId,
            $periodoLectivoId
        );

        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();
        $hoja->setTitle('ACUDIENTES');

        $encabezados = [
            'A' => 'IDENTIFICACION',

            // Acudiente B-F
            'B' => 'NOMBRE_ACUDIENTE',
            'C' => 'DOCUMENTO_ACUDIENTE',
            'D' => 'TELEFONO_ACUDIENTE',
            'E' => 'CORREO_ACUDIENTE',
            'F' => 'DIRECCION_ACUDIENTE',

            // Padre G-K
            'G' => 'NOMBRE_PADRE',
            'H' => 'DOCUMENTO_PADRE',
            'I' => 'TELEFONO_PADRE',
            'J' => 'CORREO_PADRE',
            'K' => 'DIRECCION_PADRE',

            // Madre L-P
            'L' => 'NOMBRE_MADRE',
            'M' => 'DOCUMENTO_MADRE',
            'N' => 'TELEFONO_MADRE',
            'O' => 'CORREO_MADRE',
            'P' => 'DIRECCION_MADRE',

            // Deudor económico Q-U
            'Q' => 'NOMBRE_DEUDOR_ECONOMICO',
            'R' => 'DOCUMENTO_DEUDOR_ECONOMICO',
            'S' => 'TELEFONO_DEUDOR_ECONOMICO',
            'T' => 'CORREO_DEUDOR_ECONOMICO',
            'U' => 'DIRECCION_DEUDOR_ECONOMICO',

            // Información adicional V-AC
            'V' => 'TIPO_DOCUMENTO_ACUDIENTE',
            'W' => 'LUGAR_TRABAJO_ACUDIENTE',
            'X' => 'PARENTESCO_ACUDIENTE',
            'Y' => 'TIPO_DOCUMENTO_PADRE',
            'Z' => 'LUGAR_TRABAJO_PADRE',
            'AA' => 'TIPO_DOCUMENTO_MADRE',
            'AB' => 'LUGAR_TRABAJO_MADRE',
            'AC' => 'NUMERO_FORMULARIO',
        ];

        $this->escribirEncabezados(
            $hoja,
            $encabezados
        );

        $fila = 2;

        foreach ($formularios as $formulario) {
            /*
            |--------------------------------------------------------------------------
            | Documento del estudiante
            |--------------------------------------------------------------------------
            */

            $this->escribirTexto(
                $hoja,
                "A{$fila}",
                $formulario->documento
            );

            /*
            |--------------------------------------------------------------------------
            | Acudiente
            |--------------------------------------------------------------------------
            */

            $hoja->setCellValue(
                "B{$fila}",
                $this->texto($formulario->acudiente_nombre)
            );

            $this->escribirTexto(
                $hoja,
                "C{$fila}",
                $formulario->acudiente_documento
            );

            $this->escribirTexto(
                $hoja,
                "D{$fila}",
                $formulario->acudiente_telefono
            );

            $hoja->setCellValue(
                "E{$fila}",
                $this->texto($formulario->acudiente_correo)
            );

            $hoja->setCellValue(
                "F{$fila}",
                $this->texto($formulario->acudiente_direccion)
            );

            /*
            |--------------------------------------------------------------------------
            | Padre
            |--------------------------------------------------------------------------
            */

            $hoja->setCellValue(
                "G{$fila}",
                $this->texto($formulario->padre_nombre)
            );

            $this->escribirTexto(
                $hoja,
                "H{$fila}",
                $formulario->padre_documento
            );

            $this->escribirTexto(
                $hoja,
                "I{$fila}",
                $formulario->padre_telefono
            );

            $hoja->setCellValue(
                "J{$fila}",
                $this->texto($formulario->padre_correo)
            );

            $hoja->setCellValue(
                "K{$fila}",
                $this->texto($formulario->padre_direccion)
            );

            /*
            |--------------------------------------------------------------------------
            | Madre
            |--------------------------------------------------------------------------
            */

            $hoja->setCellValue(
                "L{$fila}",
                $this->texto($formulario->madre_nombre)
            );

            $this->escribirTexto(
                $hoja,
                "M{$fila}",
                $formulario->madre_documento
            );

            $this->escribirTexto(
                $hoja,
                "N{$fila}",
                $formulario->madre_telefono
            );

            $hoja->setCellValue(
                "O{$fila}",
                $this->texto($formulario->madre_correo)
            );

            $hoja->setCellValue(
                "P{$fila}",
                $this->texto($formulario->madre_direccion)
            );

            /*
            |--------------------------------------------------------------------------
            | Deudor económico
            |--------------------------------------------------------------------------
            |
            | Se diligencia manualmente posteriormente.
            |
            */

            foreach (['Q', 'R', 'S', 'T', 'U'] as $columna) {
                $hoja->setCellValue(
                    "{$columna}{$fila}",
                    ''
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Información adicional
            |--------------------------------------------------------------------------
            */

            $hoja->setCellValue(
                "V{$fila}",
                $this->texto($formulario->acudiente_tipo_documento)
            );

            $hoja->setCellValue(
                "W{$fila}",
                $this->texto($formulario->acudiente_lugar_trabajo)
            );

            $hoja->setCellValue(
                "X{$fila}",
                $this->texto($formulario->acudiente_parentesco)
            );

            $hoja->setCellValue(
                "Y{$fila}",
                $this->texto($formulario->padre_tipo_documento)
            );

            $hoja->setCellValue(
                "Z{$fila}",
                $this->texto($formulario->padre_lugar_trabajo)
            );

            $hoja->setCellValue(
                "AA{$fila}",
                $this->texto($formulario->madre_tipo_documento)
            );

            $hoja->setCellValue(
                "AB{$fila}",
                $this->texto($formulario->madre_lugar_trabajo)
            );

            $this->escribirTexto(
                $hoja,
                "AC{$fila}",
                $formulario->numero_formulario
            );

            $fila++;
        }

        $this->aplicarPresentacion(
            $hoja,
            'A',
            'AC',
            max(2, $fila - 1),
            [
                'A' => 18,

                'B' => 28,
                'C' => 20,
                'D' => 20,
                'E' => 30,
                'F' => 35,

                'G' => 28,
                'H' => 20,
                'I' => 20,
                'J' => 30,
                'K' => 35,

                'L' => 28,
                'M' => 20,
                'N' => 20,
                'O' => 30,
                'P' => 35,

                'Q' => 28,
                'R' => 20,
                'S' => 20,
                'T' => 30,
                'U' => 35,

                'V' => 28,
                'W' => 30,
                'X' => 24,
                'Y' => 25,
                'Z' => 30,
                'AA' => 25,
                'AB' => 30,
                'AC' => 24,
            ]
        );

        return $this->descargar(
            $spreadsheet,
            'acudientes_pre_matricula'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Consulta compartida
    |--------------------------------------------------------------------------
    */

    private function obtenerFormulariosCompletados(
        int $sedeId,
        int $periodoLectivoId
    ): Collection {
        return PreMatricula::query()
            ->with([
                'eps',
            ])
            ->where('sede_id', $sedeId)
            ->where(
                'periodo_lectivo_id',
                $periodoLectivoId
            )
            ->where('estado', 'completado')
            ->orderByDesc('fecha_envio')
            ->orderByDesc('id')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Encabezados
    |--------------------------------------------------------------------------
    */

    private function escribirEncabezados(
        $hoja,
        array $encabezados
    ): void {
        foreach ($encabezados as $columna => $titulo) {
            $hoja->setCellValue(
                "{$columna}1",
                $titulo
            );
        }
    }

  

    /*
    |--------------------------------------------------------------------------
    | Presentación básica del archivo
    |--------------------------------------------------------------------------
    |
    | Estos archivos se utilizan para transferir información entre módulos.
    | No llevan colores, filtros, tablas ni formatos visuales adicionales.
    |
    */

    private function aplicarPresentacion(
        $hoja,
        string $primeraColumna,
        string $ultimaColumna,
        int $ultimaFila,
        array $anchos
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Formato general
        |--------------------------------------------------------------------------
        */

        $rangoCompleto =
            "{$primeraColumna}1:{$ultimaColumna}{$ultimaFila}";

        $hoja->getStyle($rangoCompleto)
            ->getFont()
            ->setName('Calibri')
            ->setSize(11);

        /*
        |--------------------------------------------------------------------------
        | Encabezados simples
        |--------------------------------------------------------------------------
        |
        | Sin colores, sin bordes, sin filtros y sin estilos de tabla.
        |
        */

        $hoja->getRowDimension(1)
            ->setRowHeight(15);

        /*
        |--------------------------------------------------------------------------
        | Ancho de las columnas
        |--------------------------------------------------------------------------
        */

        foreach ($anchos as $columna => $ancho) {
            $hoja->getColumnDimension($columna)
                ->setWidth($ancho);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Descargar archivo temporal
    |--------------------------------------------------------------------------
    */

    private function descargar(
        Spreadsheet $spreadsheet,
        string $prefijo
    ): BinaryFileResponse {
        $directorio = storage_path(
            'app/temp/pre-matriculas'
        );

        if (! File::isDirectory($directorio)) {
            File::makeDirectory(
                $directorio,
                0755,
                true
            );
        }

        $nombreArchivo = sprintf(
            '%s_%s.xlsx',
            $prefijo,
            now()->format('Ymd_His')
        );

        $ruta = $directorio
            . DIRECTORY_SEPARATOR
            . $nombreArchivo;

        $writer = new Xlsx($spreadsheet);
        $writer->save($ruta);

        $spreadsheet->disconnectWorksheets();

        return response()
            ->download(
                $ruta,
                $nombreArchivo,
                [
                    'Content-Type' =>
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            )
            ->deleteFileAfterSend(true);
    }

    /*
    |--------------------------------------------------------------------------
    | Utilidades
    |--------------------------------------------------------------------------
    */

    private function escribirTexto(
        $hoja,
        string $celda,
        mixed $valor
    ): void {
        $hoja->setCellValueExplicit(
            $celda,
            $this->texto($valor),
            DataType::TYPE_STRING
        );
    }

    private function texto(mixed $valor): string
    {
        return filled($valor)
            ? trim((string) $valor)
            : '';
    }

    private function generoVisible(
        mixed $genero
    ): string {
        return match (
            mb_strtolower(
                $this->texto($genero),
                'UTF-8'
            )
        ) {
            'masculino', 'm' => 'Masculino',
            'femenino', 'f' => 'Femenino',
            default => $this->texto($genero),
        };
    }
}