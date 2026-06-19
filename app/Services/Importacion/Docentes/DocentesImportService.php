<?php

namespace App\Services\Importacion\Docentes;

use App\Models\Course;
use App\Models\Docente;
use App\Models\User;
use App\Services\Importacion\BaseImportService;
use App\Services\Importacion\Contracts\ImportadorInterface;
use App\Services\Importacion\DTO\ResultadoImportacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DocentesImportService extends BaseImportService implements ImportadorInterface
{
    public function importar(string $path, array $opciones = []): ResultadoImportacion
    {
        $resultado = $this->nuevoResultado();

        $sedeId = (int) ($opciones['sede_id'] ?? 0);
        $periodoLectivoId = (int) ($opciones['periodo_lectivo_id'] ?? 0);

        if (! $sedeId) {
            $resultado->agregarError('Debe seleccionar una sede.');
            return $resultado;
        }

        if (! $periodoLectivoId) {
            $resultado->agregarError('Debe seleccionar un periodo lectivo.');
            return $resultado;
        }

        $sheet = $this->abrirHojaActiva($path);
        $ultimaFila = $sheet->getHighestDataRow();

        DB::beginTransaction();

        try {
            for ($fila = 2; $fila <= $ultimaFila; $fila++) {
                $codigo = $this->normalizarCodigo($this->valor($sheet, "A{$fila}"));
                $identificacion = $this->normalizarTexto($this->valor($sheet, "B{$fila}"));
                $nombreCompleto = $this->normalizarTexto($this->valor($sheet, "C{$fila}"));
                $direccion = $this->normalizarTexto($this->valor($sheet, "D{$fila}"));
                $telefono = $this->normalizarTexto($this->valor($sheet, "E{$fila}"));
                $especialidad = $this->normalizarTexto($this->valor($sheet, "F{$fila}"));
                $escalafon = $this->normalizarTexto($this->valor($sheet, "G{$fila}"));
                $cursoDirector = $this->normalizarCodigo($this->valor($sheet, "K{$fila}"));

                if ($codigo === '' && $identificacion === '' && $nombreCompleto === '') {
                    continue;
                }

                $resultado->filasLeidas++;

                if ($codigo === '') {
                    $this->agregarError($fila, 'El docente no tiene código.');
                    $resultado->omitidos++;
                    continue;
                }

                if ($identificacion === '') {
                    $identificacion = '0';
                }

                if (trim($identificacion) === '000') {
                    $identificacion = '0';
                }

                if ($nombreCompleto === '') {
                    $this->agregarError($fila, "El docente código {$codigo} no tiene nombre.");
                    $resultado->omitidos++;
                    continue;
                }

                $partesNombre = $this->dividirNombreCompleto($nombreCompleto);

                $direccionCursoId = null;

                if ($cursoDirector !== '' && $cursoDirector !== '000') {
                    $course = Course::query()
                        ->where('sede_id', $sedeId)
                        ->where('periodo_lectivo_id', $periodoLectivoId)
                        ->where('curso', $cursoDirector)
                        ->first();

                    if (! $course) {
                        $this->agregarError($fila, "El curso {$cursoDirector} de dirección de curso no existe.");
                    } else {
                        $direccionCursoId = $course->id;
                    }
                }

                $correo = 'a@a.com';

                $user = User::query()
                    ->where('name', 'Docente')
                    ->first();

                if (! $user) {
                    $user = User::create([
                        'name' => 'Docente',
                        'email' => 'docente@rembrandt.local',
                        'password' => Hash::make('12345678'),
                        'sede_id' => $sedeId,
                    ]);

                    $user->assignRole('docente');
                }

                $existente = Docente::query()
                    ->where('codigo', $codigo)
                    ->first();

                Docente::updateOrCreate(
                    ['codigo' => $codigo],
                    [
                        'codigo' => $codigo,
                        'user_id' => $user->id,
                        'identificacion' => $identificacion,
                        'nombres' => $partesNombre['nombres'],
                        'apellidos' => $partesNombre['apellidos'],
                        'telefono' => $telefono ?: '*',
                        'correo' => $correo,
                        'direccion' => $direccion ?: null,
                        'especialidad' => $especialidad ?: null,
                        'escalafon' => $escalafon ?: null,
                        'direccion_curso_id' => $direccionCursoId,
                        'estado' => 'activo',
                        
                        
                    ]
                );

                if ($existente) {
                    $resultado->actualizados++;
                } else {
                    $resultado->creados++;
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

    protected function dividirNombreCompleto(string $nombreCompleto): array
    {
        $partes = preg_split('/\s+/', trim($nombreCompleto));

        if (count($partes) <= 1) {
            return [
                'nombres' => $nombreCompleto,
                'apellidos' => '*',
            ];
        }

        if (count($partes) === 2) {
            return [
                'nombres' => $partes[0],
                'apellidos' => $partes[1],
            ];
        }

        $apellidos = array_slice($partes, -2);
        $nombres = array_slice($partes, 0, -2);

        return [
            'nombres' => implode(' ', $nombres),
            'apellidos' => implode(' ', $apellidos),
        ];
    }
}