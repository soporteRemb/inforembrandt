<?php

namespace App\Services\Boletines;

use App\Models\BoletinGenerado;
use App\Models\Course;
use App\Models\PeriodoAcademico;
use App\Models\PeriodoLectivo;
use App\Models\Student;
use App\Models\NotaEstudiante;
use App\Models\PensumAcademico;
use App\Models\RangoDesempenoNota;
use App\Models\BoletinDesempeno;
use App\Models\BoletinRecomendacion;
use App\Models\BoletinRecomendacionEstudiante;
use App\Models\Docente;
use App\Models\DocenteAsignatura;


use Illuminate\Support\Facades\Schema;


use InvalidArgumentException;

class BoletinDataService
{
    public function generar(int $studentId, int $periodoAcademicoId): array
    {
        $student = Student::query()
            ->with(['course', 'periodoLectivo'])
            ->findOrFail($studentId);

        $course = $student->course;

        if (! $course) {
            throw new InvalidArgumentException('El estudiante no tiene curso asignado.');
        }

        $periodoLectivo = $student->periodoLectivo;

        if (! $periodoLectivo) {
            throw new InvalidArgumentException('El estudiante no tiene período lectivo asignado.');
        }

        $periodoAcademico = PeriodoAcademico::query()
            ->where('id', $periodoAcademicoId)
            ->where('periodo_lectivo_id', $periodoLectivo->id)
            ->firstOrFail();

        $boletin = $this->buscarBoletin($student, $periodoLectivo, $periodoAcademico, $course);

        return [
            'estudiante' => $this->datosEstudiante($student),
            'curso' => $this->datosCurso($course),
            'periodo' => $this->datosPeriodo($periodoLectivo, $periodoAcademico),
            'boletin' => $this->datosBoletin($boletin),
            'asignaturas' => $this->datosAsignaturas($student, $course, $periodoLectivo, $periodoAcademico),
            'desempenos' => $this->datosDesempenos($student, $course, $periodoLectivo, $periodoAcademico),
            'mejoramientos' => $this->datosMejoramientos($student, $course, $periodoLectivo, $periodoAcademico),
            'perfil' => $this->datosRecomendacionesEstudiante($student, $periodoAcademico, 'perfil'),
            'acompanamiento' => $this->datosRecomendacionesEstudiante($student, $periodoAcademico, 'acompanamiento'),
            'convenciones' => $this->datosConvenciones(),
        ];
    }

    private function buscarBoletin(
        Student $student,
        PeriodoLectivo $periodoLectivo,
        PeriodoAcademico $periodoAcademico,
        Course $course
    ): ?BoletinGenerado {
        return BoletinGenerado::query()
            ->where('student_id', $student->id)
            ->where('periodo_lectivo_id', $periodoLectivo->id)
            ->where('periodo_academico_id', $periodoAcademico->id)
            ->where('course_id', $course->id)
            ->first();
    }

    private function datosEstudiante(Student $student): array
    {
        return [
            'id' => $student->id,
            'codigo' => $student->codigo,
            'nombre' => collect([
                $student->primer_nombre,
                $student->segundo_nombre,
                $student->primer_apellido,
                $student->segundo_apellido,
            ])->filter()->implode(' '),
            'documento' => $student->documento,
        ];
    }

    protected function datosCurso($course): array
    {
        return [
            'id' => $course?->id,
            'codigo' => $course?->curso,
            'nombre' => $course?->nombre ?? $course?->curso,
            'grado' => $course?->grado,
            'jornada' => 'Completa',

            'grado_curso' => trim(($course?->nombre ?? $this->nombreGrado($course?->grado)) . ' - ' . ($course?->curso ?? '')),
            'director_curso' => $this->directorCurso($course),
        ];
    }

    protected function nombreGrado($grado): string
    {
        $gradoTexto = trim((string) ($grado ?? ''));

        return match (mb_strtolower($gradoTexto)) {
            'prejardin', 'prejardín', 'pre jardin', 'pre jardín', '-2' => 'Prejardín',
            'jardin', 'jardín', '-1' => 'Jardín',
            'transicion', 'transición', '0' => 'Transición',
            '1' => 'Primero',
            '2' => 'Segundo',
            '3' => 'Tercero',
            '4' => 'Cuarto',
            '5' => 'Quinto',
            '6' => 'Sexto',
            '7' => 'Séptimo',
            '8' => 'Octavo',
            '9' => 'Noveno',
            '10' => 'Décimo',
            '11' => 'Once',
            default => $gradoTexto,
        };
    }

    protected function datosPeriodo($periodoLectivo, $periodoAcademico): array
    {
        return [
            'lectivo_id' => $periodoLectivo?->id,
            'academico_id' => $periodoAcademico?->id,

            'lectivo' => $periodoLectivo?->nombre,
            'academico' => $periodoAcademico?->nombre,
            'numero' => $periodoAcademico?->numero,

            'academico_corto' => $this->nombrePeriodoCorto($periodoAcademico?->numero),
        ];
    }

    protected function nombrePeriodoCorto($numero): string
    {
        return match ((int) $numero) {
            1 => 'PRIMERO',
            2 => 'SEGUNDO',
            3 => 'TERCERO',
            4 => 'CUARTO',
            default => strtoupper((string) ($numero ?? '')),
        };
    }

    private function datosBoletin(?BoletinGenerado $boletin): array
    {
        return [
            'observaciones' => $boletin?->observaciones ?? '',
            'estado' => $boletin?->estado ?? 'disponible',
            'codigos_perfil' => $boletin?->codigos_perfil ?? [],
            'codigos_acompanamiento' => $boletin?->codigos_acompanamiento ?? [],
        ];
    }

    private function datosAsignaturas(
        Student $student,
        Course $course,
        PeriodoLectivo $periodoLectivo,
        PeriodoAcademico $periodoAcademico
    ): array {
        $pensum = PensumAcademico::query()
            ->where('sede_id', $student->sede_id)
            ->where('periodo_lectivo_id', $periodoLectivo->id)
            ->where('grado', $course->grado)
            ->where('estado', 'activo')
            ->where('tipo', 'asignatura')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $periodoActual = (int) $periodoAcademico->numero;

        $notasTodosPeriodos = NotaEstudiante::query()
            ->where('student_id', $student->id)
            ->whereIn('periodo', range(1, $periodoActual))
            ->whereIn('pensum_academico_id', $pensum->pluck('id'))
            ->get()
            ->groupBy('pensum_academico_id');

        $rangos = $this->rangosDesempeno();

        return $pensum
            ->map(function ($asignatura) use ($notasTodosPeriodos, $rangos, $course, $periodoActual) {
                $notasAsignatura = $notasTodosPeriodos->get($asignatura->id, collect());

                $notaActual = $notasAsignatura
                    ->firstWhere('periodo', $periodoActual);

                $notasPeriodos = [];

                foreach (range(1, $periodoActual) as $numeroPeriodo) {
                    $registroPeriodo = $notasAsignatura->firstWhere('periodo', $numeroPeriodo);

                    $notasPeriodos[$numeroPeriodo] = $registroPeriodo?->nota;
                }

                $notasValidas = collect($notasPeriodos)
                    ->filter(fn ($nota) => $nota !== null && $nota !== '' && is_numeric($nota))
                    ->map(fn ($nota) => (float) $nota)
                    ->values();

                $promedioFinal = $notasValidas->isNotEmpty()
                    ? round($notasValidas->avg(), 0)
                    : null;

                return [
                    'pensum_academico_id' => $asignatura->id,
                    'orden' => $asignatura->orden,
                    'codigo' => $asignatura->codigo,
                    'nombre' => $asignatura->nombre,
                    'nombre_corto' => $asignatura->nombre_corto ?: $asignatura->nombre,
                    'ih' => $asignatura->intensidad_horaria ?? '',
                    'fallas' => $notaActual?->fallas ?? 0,
                    'pgc' => $notaActual?->pgc ?? '',
                    'nota' => $notaActual?->nota ?? '',
                    'desempeno' => $this->nombreDesempeno($notaActual?->nota, $rangos),

                    'notas_periodos' => $notasPeriodos,
                    'promedio_final' => $promedioFinal,

                    'docente' => $this->docenteAsignatura($asignatura, $course),
                    'mejoramientos' => [
                        '01' => $notaActual?->mejoramiento_01,
                        '02' => $notaActual?->mejoramiento_02,
                        '03' => $notaActual?->mejoramiento_03,
                        '04' => $notaActual?->mejoramiento_04,
                    ],
                ];
            })
            ->toArray();
    }

    private function rangosDesempeno(): array
    {
        return RangoDesempenoNota::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get()
            ->map(fn ($rango) => [
                'nombre' => $rango->nombre,
                'desde' => (float) $rango->desde,
                'hasta' => (float) $rango->hasta,
            ])
            ->toArray();
    }

    private function nombreDesempeno($nota, array $rangos): string
    {
        if ($nota === null || $nota === '' || ! is_numeric($nota)) {
            return '';
        }

        $nota = (float) $nota;

        foreach ($rangos as $rango) {
            if ($nota >= $rango['desde'] && $nota <= $rango['hasta']) {
                return $rango['nombre'];
            }
        }

        return '';
    }


    private function datosDesempenos(
        Student $student,
        Course $course,
        PeriodoLectivo $periodoLectivo,
        PeriodoAcademico $periodoAcademico
    ): array {
        $pensum = PensumAcademico::query()
            ->where('sede_id', $student->sede_id)
            ->where('periodo_lectivo_id', $periodoLectivo->id)
            ->where('grado', $course->grado)
            ->where('estado', 'activo')
            ->where('tipo', 'asignatura')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $desempenos = BoletinDesempeno::query()
            ->where('sede_id', $student->sede_id)
            ->where('periodo_lectivo_id', $periodoLectivo->id)
            ->where('grado', $course->grado)
            ->where('periodo_academico', $periodoAcademico->numero)
            ->whereIn('pensum_academico_id', $pensum->pluck('id'))
            ->get()
            ->keyBy('pensum_academico_id');

        return $pensum
            ->map(function ($asignatura) use ($desempenos) {
                $registro = $desempenos->get($asignatura->id);

                $items = collect([
                    $registro?->desempeno_1,
                    $registro?->desempeno_2,
                    $registro?->desempeno_3,
                    $registro?->desempeno_4,
                ])->filter()->values()->toArray();

                return [
                    'pensum_academico_id' => $asignatura->id,
                    'asignatura' => $asignatura->nombre_corto ?: $asignatura->nombre,
                    'items' => $items,
                ];
            })
            ->filter(fn ($fila) => count($fila['items']) > 0)
            ->values()
            ->toArray();
    }


    private function datosMejoramientos(
        Student $student,
        Course $course,
        PeriodoLectivo $periodoLectivo,
        PeriodoAcademico $periodoAcademico
    ): array {
        $pensum = PensumAcademico::query()
            ->where('sede_id', $student->sede_id)
            ->where('periodo_lectivo_id', $periodoLectivo->id)
            ->where('grado', $course->grado)
            ->where('estado', 'activo')
            ->where('tipo', 'asignatura')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $notas = NotaEstudiante::query()
            ->where('student_id', $student->id)
            ->where('periodo', $periodoAcademico->numero)
            ->whereIn('pensum_academico_id', $pensum->pluck('id'))
            ->get()
            ->keyBy('pensum_academico_id');

        $recomendaciones = BoletinRecomendacion::query()
            ->where('sede_id', $student->sede_id)
            ->where('periodo_lectivo_id', $periodoLectivo->id)
            ->where('grado', $course->grado)
            ->where('periodo_academico', $periodoAcademico->numero)
            ->where('tipo', 'mejoramiento')
            ->where('activo', true)
            ->whereIn('pensum_academico_id', $pensum->pluck('id'))
            ->get()
            ->groupBy('pensum_academico_id');

        $resultado = [];

        foreach ($pensum as $asignatura) {
            $nota = $notas->get($asignatura->id);

            $codigos = collect([
                $nota?->mejoramiento_01,
                $nota?->mejoramiento_02,
                $nota?->mejoramiento_03,
                $nota?->mejoramiento_04,
            ])->filter()->values();

            foreach ($codigos as $codigo) {
                $recomendacion = $recomendaciones
                    ->get($asignatura->id, collect())
                    ->firstWhere('codigo', (string) $codigo);

                $resultado[] = [
                    'asignatura' => $asignatura->nombre_corto ?: $asignatura->nombre,
                    'codigo' => $codigo,
                    'descripcion' => $recomendacion?->descripcion ?? 'Código no encontrado',
                ];
            }
        }

        return $resultado;
    }

    private function datosRecomendacionesEstudiante(
        Student $student,
        PeriodoAcademico $periodoAcademico,
        string $tipo
    ): array {
        return BoletinRecomendacionEstudiante::query()
            ->with('recomendacion')
            ->where('student_id', $student->id)
            ->where('periodo_academico', $periodoAcademico->numero)
            ->whereHas('recomendacion', fn ($query) => $query->where('tipo', $tipo))
            ->orderBy('orden')
            ->get()
            ->map(fn ($asignacion) => [
                'codigo' => $asignacion->recomendacion?->codigo,
                'descripcion' => $asignacion->recomendacion?->descripcion,
                'orden' => $asignacion->orden,
            ])
            ->toArray();
    }

    private function datosConvenciones(): array
    {
        return \App\Models\RangoDesempenoNota::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get()
            ->map(fn ($rango) => [
                'nombre' => $rango->nombre,
                'convencion' => $rango->convencion,
                'descripcion_convencion' => $rango->descripcion_convencion,
                'desde' => $rango->desde,
                'hasta' => $rango->hasta,
            ])
            ->toArray();
    }

    protected function directorCurso($course): string
    {
        if (! $course) {
            return '';
        }

        $query = Docente::query()
            ->where('estado', 'activo');

        if (Schema::hasColumn('docentes', 'direccion_curso_id')) {
            $query->where('direccion_curso_id', $course->id);
        } elseif (Schema::hasColumn('docentes', 'course_id')) {
            $query->where('course_id', $course->id);
        } elseif (Schema::hasColumn('docentes', 'curso_id')) {
            $query->where('curso_id', $course->id);
        } else {
            return '';
        }

        $docente = $query->first();

        if (! $docente) {
            return '';
        }

        return collect([
            $docente->nombres ?? null,
            $docente->apellidos ?? null,
        ])->filter()->implode(' ');
    }

    private function docenteAsignatura(
        PensumAcademico $pensum,
        Course $course
    ): string {

        $docente = $pensum->docentePreferido($course->id);

        if (! $docente) {
            return '';
        }

        return trim(
            $docente->nombres . ' ' .
            $docente->apellidos
        );
    }

}