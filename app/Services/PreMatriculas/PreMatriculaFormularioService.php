<?php

namespace App\Services\PreMatriculas;

use App\Models\PreMatricula;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PreMatriculaFormularioService
{
    public function __construct(
        private readonly PreMatriculaService $preMatriculaService,
        private readonly PreMatriculaValidacionService $validacionService,
    ) {
    }

    public function obtenerFormularioUsuario(User $usuario): ?PreMatricula
    {
        return PreMatricula::query()
            ->with([
                'sede',
                'periodoLectivo',
                'eps',
            ])
            ->where('user_id', $usuario->id)
            ->latest('id')
            ->first();
    }

    public function verificarDisponibilidad(
        User $usuario,
        PreMatricula $preMatricula
    ): void {
        if ($preMatricula->estaCompletada()) {
            throw ValidationException::withMessages([
                'formulario' => 'Su formulario ya fue enviado. Si requiere cambios, comuníquese con la institución educativa.',
            ]);
        }

        if ($preMatricula->estaVencida() || $preMatricula->plazoVencido()) {
            throw ValidationException::withMessages([
                'formulario' => 'El tiempo para diligenciar el formulario ha finalizado. Comuníquese con la institución educativa.',
            ]);
        }

        if (! $this->validacionService->puedeDiligenciar(
            $usuario,
            $preMatricula
        )) {
            throw ValidationException::withMessages([
                'formulario' => 'El formulario no se encuentra disponible.',
            ]);
        }
    }

    public function obtenerEpsActivas(): Collection
    {
        return \App\Models\Eps::query()
            ->orderBy('nombre')
            ->get();
    }

    public function obtenerGrados(): array
    {
        return [
            'Preescolar',
            'Jardín',
            'Transición',
            'Primero',
            'Segundo',
            'Tercero',
            'Cuarto',
            'Quinto',
            'Sexto',
            'Séptimo',
            'Octavo',
            'Noveno',
            'Décimo',
            'Undécimo',
        ];
    }

    public function calcularEdad(
        string|\DateTimeInterface|null $fechaNacimiento
    ): ?int {
        if (blank($fechaNacimiento)) {
            return null;
        }

        return Carbon::parse($fechaNacimiento)->age;
    }

    public function copiarResponsableComoAcudiente(
        array $datos,
        string $origen
    ): array {
        if (! in_array($origen, ['padre', 'madre'], true)) {
            return $datos;
        }

        $datos['acudiente_origen'] = $origen;
        $datos['acudiente_parentesco'] = ucfirst($origen);
        $datos['acudiente_nombre'] = $datos[$origen . '_nombre'] ?? null;
        $datos['acudiente_telefono'] = $datos[$origen . '_telefono'] ?? null;
        $datos['acudiente_tipo_documento'] = $datos[$origen . '_tipo_documento'] ?? null;
        $datos['acudiente_documento'] = $datos[$origen . '_documento'] ?? null;
        $datos['acudiente_lugar_trabajo'] = $datos[$origen . '_lugar_trabajo'] ?? null;
        $datos['acudiente_correo'] = $datos[$origen . '_correo'] ?? null;
        $datos['acudiente_direccion'] = $datos[$origen . '_direccion'] ?? null;

        return $datos;
    }

    public function prepararDatosFormulario(
        PreMatricula $preMatricula
    ): array {
        $datos = $preMatricula->only([
            'nombres',
            'apellidos',
            'tipo_documento',
            'documento',
            'ciudad_expedicion',
            'fecha_nacimiento',
            'edad',
            'ciudad_nacimiento',
            'genero',
            'numero_hermanos',
            'telefono',
            'correo',
            'direccion',
            'rh',
            'eps_id',
            'eps_otro',
            'telefono_emergencia',
            'grado_aspira',
            'institucion_anterior',
            'condicion_ingreso',

            'padre_nombre',
            'padre_telefono',
            'padre_tipo_documento',
            'padre_documento',
            'padre_lugar_trabajo',
            'padre_correo',
            'padre_direccion',

            'madre_nombre',
            'madre_telefono',
            'madre_tipo_documento',
            'madre_documento',
            'madre_lugar_trabajo',
            'madre_correo',
            'madre_direccion',

            'acudiente_origen',
            'acudiente_parentesco',
            'acudiente_nombre',
            'acudiente_telefono',
            'acudiente_tipo_documento',
            'acudiente_documento',
            'acudiente_lugar_trabajo',
            'acudiente_correo',
            'acudiente_direccion',
        ]);

        if ($preMatricula->fecha_nacimiento) {
            $datos['fecha_nacimiento'] =
                $preMatricula->fecha_nacimiento->format('Y-m-d');
        } else {
            $datos['fecha_nacimiento'] = null;
        }

        $camposTexto = [
            'nombres',
            'apellidos',
            'tipo_documento',
            'documento',
            'ciudad_expedicion',
            'ciudad_nacimiento',
            'genero',
            'telefono',
            'correo',
            'direccion',
            'rh',
            'telefono_emergencia',
            'grado_aspira',
            'institucion_anterior',
            'condicion_ingreso',

            'padre_nombre',
            'padre_telefono',
            'padre_tipo_documento',
            'padre_documento',
            'padre_lugar_trabajo',
            'padre_correo',
            'padre_direccion',

            'madre_nombre',
            'madre_telefono',
            'madre_tipo_documento',
            'madre_documento',
            'madre_lugar_trabajo',
            'madre_correo',
            'madre_direccion',

            'acudiente_origen',
            'acudiente_parentesco',
            'acudiente_nombre',
            'acudiente_telefono',
            'acudiente_tipo_documento',
            'acudiente_documento',
            'acudiente_lugar_trabajo',
            'acudiente_correo',
            'acudiente_direccion',
        ];

        foreach ($camposTexto as $campo) {
            $datos[$campo] = (string) ($datos[$campo] ?? '');
        }

        $datos['eps_id'] = filled($datos['eps_id'] ?? null)
            ? (string) $datos['eps_id']
            : '';

        $datos['numero_hermanos'] =
            $datos['numero_hermanos'] ?? 0;

        $datos['edad'] = filled($datos['edad'] ?? null)
            ? (int) $datos['edad']
            : null;

        if (blank($datos['acudiente_origen'])) {
            $datos['acudiente_origen'] = 'otro';
        }

        return $datos;
    }

    public function enviar(
        PreMatricula $preMatricula,
        array $datos,
        User $usuario
    ): PreMatricula {
        $this->verificarDisponibilidad(
            $usuario,
            $preMatricula
        );

        $datos['edad'] = $this->calcularEdad(
            $datos['fecha_nacimiento'] ?? null
        );

        if (
            in_array(
                $datos['acudiente_origen'] ?? null,
                ['padre', 'madre'],
                true
            )
        ) {
            $datos = $this->copiarResponsableComoAcudiente(
                $datos,
                $datos['acudiente_origen']
            );
        }

        $datos = $this->normalizarDatos($datos);

        return $this->preMatriculaService->completarFormulario(
            $preMatricula,
            $datos,
            $usuario
        );
    }

    private function normalizarDatos(array $datos): array
    {
        $camposMayuscula = [
            'nombres',
            'apellidos',
            'ciudad_expedicion',
            'ciudad_nacimiento',
            'direccion',
            'grado_aspira',
            'institucion_anterior',
            'eps_otro',

            'documento',
            'telefono',
            'telefono_emergencia',

            'padre_nombre',
            'padre_telefono',
            'padre_documento',
            'padre_lugar_trabajo',
            'padre_direccion',

            'madre_nombre',
            'madre_telefono',
            'madre_documento',
            'madre_lugar_trabajo',
            'madre_direccion',

            'acudiente_parentesco',
            'acudiente_nombre',
            'acudiente_telefono',
            'acudiente_documento',
            'acudiente_lugar_trabajo',
            'acudiente_direccion',
        ];

        foreach ($camposMayuscula as $campo) {
            if (filled($datos[$campo] ?? null)) {
                $datos[$campo] = mb_strtoupper(
                    trim((string) $datos[$campo]),
                    'UTF-8'
                );
            }
        }

        return $datos;
    }
}