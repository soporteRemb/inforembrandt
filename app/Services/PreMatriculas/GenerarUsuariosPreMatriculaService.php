<?php

namespace App\Services\PreMatriculas;

use App\Models\PeriodoLectivo;
use App\Models\Student;
use App\Models\User;
use App\Models\Eps;
use App\Models\Guardian;
use App\Models\PreMatricula;
use App\Services\DocumentosEstudiante\CopiarDocumentosEstudiantePreMatriculaService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GenerarUsuariosPreMatriculaService
{
    /**
     * Analiza los estudiantes antiguos que participarían
     * en una generación masiva.
     *
     * IMPORTANTE:
     * Este método NO crea ni modifica ningún registro.
     */
    public function analizar(
        int $sedeId,
        int $periodoDestinoId
    ): array {
        $periodoDestino = PeriodoLectivo::query()
            ->where('id', $periodoDestinoId)
            ->where('sede_id', $sedeId)
            ->first();

        if (! $periodoDestino) {
            throw ValidationException::withMessages([
                'periodo_lectivo_id' =>
                    'El periodo lectivo seleccionado no pertenece a la sede indicada.',
            ]);
        }

        if (! preg_match(
            '/^\d{4}$/',
            trim((string) $periodoDestino->nombre)
        )) {
            throw ValidationException::withMessages([
                'periodo_lectivo_id' =>
                    'El periodo lectivo debe corresponder a un año válido de cuatro dígitos.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Determinar periodo anterior dinámicamente
        |--------------------------------------------------------------------------
        |
        | Ejemplos:
        | 2027 → 2026
        | 2028 → 2027
        | 2029 → 2028
        |
        | No depende del año actual y no existen años quemados.
        |
        */

        $anioDestino = (int) $periodoDestino->nombre;
        $anioOrigen = $anioDestino - 1;

        $periodoOrigen = PeriodoLectivo::query()
            ->where('sede_id', $sedeId)
            ->where('nombre', (string) $anioOrigen)
            ->first();

        if (! $periodoOrigen) {
            throw ValidationException::withMessages([
                'periodo_lectivo_id' =>
                    "No existe el periodo lectivo {$anioOrigen} para la sede seleccionada.",
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Estudiantes antiguos
        |--------------------------------------------------------------------------
        |
        | Student es únicamente fuente de consulta.
        | Aquí NO se modifica ningún estudiante.
        |
        | También consultamos codigo para comprobar que los estudiantes antiguos
        | conservarán su código institucional al preparar la nueva campaña.
        |
        */

        $estudiantes = Student::query()
            ->where('sede_id', $sedeId)
            ->where(
                'periodo_lectivo_id',
                $periodoOrigen->id
            )
            ->where('estado', 'activo')
            ->get([
                'id',
                'documento',
                'codigo',
            ]);

        $conDocumento = $estudiantes->filter(
            fn (Student $student): bool =>
                filled(trim((string) $student->documento))
        );

        $sinDocumento = $estudiantes->filter(
            fn (Student $student): bool =>
                blank(trim((string) $student->documento))
        );

        $conCodigo = $estudiantes->filter(
            fn (Student $student): bool =>
                filled(trim((string) $student->codigo))
        );

        $sinCodigo = $estudiantes->filter(
            fn (Student $student): bool =>
                blank(trim((string) $student->codigo))
        );

        /*
        |--------------------------------------------------------------------------
        | Usuarios que ya existen
        |--------------------------------------------------------------------------
        |
        | En el sistema el campo email funciona visualmente como "Usuario".
        | Para los temporales antiguos será el documento del estudiante.
        |
        | MySQL puede comparar este campo sin distinguir mayúsculas/minúsculas.
        | Normalizamos también en PHP para que ambos lados utilicen exactamente
        | la misma regla.
        |
        | Ejemplo:
        | Student: 1000ABC
        | User:    1000abc
        |
        | Ambos representan el mismo usuario.
        |
        | Todavía NO modificamos ni creamos User.
        |
        */

        $documentos = $conDocumento
            ->pluck('documento')
            ->map(
                fn ($documento) =>
                    trim((string) $documento)
            )
            ->unique()
            ->values();

        $usuariosExistentes = User::query()
            ->whereIn('email', $documentos)
            ->pluck('email')
            ->map(
                fn ($email) =>
                    $this->normalizarIdentificador($email)
            )
            ->unique()
            ->all();

        $usuariosExistentesLookup = array_fill_keys(
            $usuariosExistentes,
            true
        );

        $yaExisten = $conDocumento->filter(
            fn (Student $student): bool =>
                isset(
                    $usuariosExistentesLookup[
                        $this->normalizarIdentificador(
                            $student->documento
                        )
                    ]
                )
        );

        $disponibles = $conDocumento->reject(
            fn (Student $student): bool =>
                isset(
                    $usuariosExistentesLookup[
                        $this->normalizarIdentificador(
                            $student->documento
                        )
                    ]
                )
        );

        return [
            'sede_id' => $sedeId,

            'periodo_destino' => [
                'id' => $periodoDestino->id,
                'nombre' => $periodoDestino->nombre,
            ],

            'periodo_origen' => [
                'id' => $periodoOrigen->id,
                'nombre' => $periodoOrigen->nombre,
            ],

            'estudiantes_encontrados' =>
                $estudiantes->count(),

            'con_documento' =>
                $conDocumento->count(),

            'sin_documento' =>
                $sinDocumento->count(),

            'con_codigo' =>
                $conCodigo->count(),

            'sin_codigo' =>
                $sinCodigo->count(),

            'usuarios_ya_existentes' =>
                $yaExisten->count(),

            'disponibles_para_crear' =>
                $disponibles->count(),
        ];
    }

    public function inspeccionarEstudiante(
        int $studentId
    ): array {
        $student = Student::query()
            ->with([
                'course',
                'guardians' => function ($query) {
                    $query->wherePivot('estado', 'activo');
                },
            ])
            ->findOrFail($studentId);

        $responsables = [
            'padre' => null,
            'madre' => null,
            'acudiente' => null,
        ];

        foreach ($student->guardians as $guardian) {
            $tipo = mb_strtolower(
                trim((string) $guardian->pivot->tipo),
                'UTF-8'
            );

            if (! array_key_exists($tipo, $responsables)) {
                continue;
            }

            $responsables[$tipo] = [
                'id' => $guardian->id,
                'nombre' => $guardian->nombre,
                'tipo_documento' => $guardian->tipo_documento,
                'documento' => $guardian->documento,
                'telefono' => $guardian->telefono,
                'correo' => $guardian->correo,
                'direccion' => $guardian->direccion,
                'lugar_trabajo' => $guardian->lugar_trabajo,
                'parentesco' => $guardian->parentesco,
            ];
        }

        $identificador = $this->normalizarIdentificador(
            $student->documento
        );

        $usuario = User::query()
            ->whereRaw(
                'UPPER(TRIM(email)) = ?',
                [$identificador]
            )
            ->first();

        return [
            'estudiante' => [
                'id' => $student->id,
                'sede_id' => $student->sede_id,
                'periodo_lectivo_id' => $student->periodo_lectivo_id,
                'codigo' => $student->codigo,
                'documento' => $student->documento,
                'identificador_normalizado' => $identificador,
                'tipo_documento' => $student->tipo_documento,
                'nombres' => $student->nombres,
                'apellidos' => $student->apellidos,
                'fecha_nacimiento' => $student->fecha_nacimiento?->format('Y-m-d'),
                'edad' => $student->edad,
                'ciudad_expedicion' => $student->ciudad_expedicion,
                'ciudad_nacimiento' => $student->ciudad_nacimiento,
                'genero' => $student->sexo,
                'numero_hermanos' => $student->numero_hermanos,
                'correo' => $student->correo,
                'rh' => $student->rh,
                'eps' => $student->eps,
                'telefono_emergencia' => $student->telefono_emergencia,
                'institucion_anterior' => $student->institucion_anterior,
                'grado_actual' => $student->course?->grado
                    ?? $student->ultimo_grado,
            ],

            'usuario_existente' => $usuario
                ? [
                    'id' => $usuario->id,
                    'email' => $usuario->email,
                    'tipo_usuario' => $usuario->tipo_usuario,
                    'is_active' => $usuario->is_active,
                    'roles' => $usuario->getRoleNames()->values()->all(),
                ]
                : null,

            'responsables' => $responsables,
        ];
    }

    public function resolverUsuarioTemporalEstudiante(
        int $studentId,
        int $sedeId,
        string $fechaVencimiento
    ): array {
        $student = Student::query()
            ->findOrFail($studentId);

        $identificador = $this->normalizarIdentificador(
            $student->documento
        );

        if ($identificador === '') {
            throw ValidationException::withMessages([
                'documento' => 'El estudiante no tiene un documento válido para crear el usuario temporal.',
            ]);
        }

        $usuario = User::query()
            ->whereRaw(
                'UPPER(TRIM(email)) = ?',
                [$identificador]
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Usuario existente
        |--------------------------------------------------------------------------
        */
        if ($usuario) {
            $esTemporal = $usuario->hasRole('temporal');

            if (! $esTemporal) {
                throw ValidationException::withMessages([
                    'usuario' => "El identificador {$identificador} ya pertenece a un usuario que no tiene el rol temporal.",
                ]);
            }

            $usuario->forceFill([
                'email' => $identificador,
                'name' => $this->nombreCompletoEstudiante(
                    $student
                ),
                'sede_id' => $sedeId,
                'tipo_usuario' => 'temporal',
                'is_active' => true,
                'expires_at' => $fechaVencimiento,
            ])->save();

            $usuario->sedes()->sync([
                $sedeId,
            ]);

            return [
                'usuario' => $usuario->fresh(),
                'accion' => 'reutilizado',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Usuario nuevo
        |--------------------------------------------------------------------------
        */
        $passwordInicial = $this->obtenerPasswordInicial(
            $identificador
        );

        $usuario = User::create([
            'name' => $this->nombreCompletoEstudiante(
                $student
            ),
            'email' => $identificador,
            'correo' => null,
            'password' => Hash::make(
                $passwordInicial
            ),
            'is_active' => true,
            'expires_at' => $fechaVencimiento,
            'sede_id' => $sedeId,
            'tipo_usuario' => 'temporal',
        ]);

        $usuario->assignRole('temporal');

        $usuario->sedes()->sync([
            $sedeId,
        ]);

        return [
            'usuario' => $usuario->fresh(),
            'accion' => 'creado',
            'password_inicial' => $passwordInicial,
        ];
    }

    private function nombreCompletoEstudiante(
        Student $student
    ): string {
        $nombre = trim(
            (string) $student->nombres
            . ' '
            . (string) $student->apellidos
        );

        return $this->normalizarTexto(
            $nombre
        ) ?? $this->normalizarIdentificador(
            $student->documento
        );
    }

    private function obtenerPasswordInicial(
        string $identificador
    ): string {
        if (mb_strlen($identificador, 'UTF-8') < 4) {
            throw ValidationException::withMessages([
                'documento' => 'El documento del estudiante debe tener al menos 4 caracteres para generar la contraseña inicial.',
            ]);
        }

        return mb_substr(
            $identificador,
            -4,
            null,
            'UTF-8'
        );
    }


    public function prepararMasivamente(
        int $sedeId,
        int $periodoDestinoId,
        string $fechaVencimiento,
        User $usuarioAdministrador
    ): array {
        $analisis = $this->analizar(
            $sedeId,
            $periodoDestinoId
        );

        $estudiantes = Student::query()
            ->where('sede_id', $sedeId)
            ->where(
                'periodo_lectivo_id',
                $analisis['periodo_origen']['id']
            )
            ->where('estado', 'activo')
            ->orderBy('id')
            ->get();

        $resultado = [
            'total_estudiantes' => $estudiantes->count(),

            'usuarios_creados' => 0,
            'usuarios_reutilizados' => 0,

            'pre_matriculas_creadas' => 0,
            'pre_matriculas_omitidas' => 0,

            'documentos_encontrados' => 0,
            'documentos_copiados' => 0,
            'documentos_ya_existentes' => 0,

            'sin_documento' => 0,
            'conflictos' => 0,
            'errores' => 0,

            'detalle_conflictos' => [],
            'detalle_errores' => [],

            'periodo_origen_id' =>
                $analisis['periodo_origen']['id'],

            'periodo_origen' =>
                $analisis['periodo_origen']['nombre'],

            'periodo_destino_id' =>
                $analisis['periodo_destino']['id'],

            'periodo_destino' =>
                $analisis['periodo_destino']['nombre'],
        ];

        foreach ($estudiantes as $student) {
            $identificador = $this->normalizarIdentificador(
                $student->documento
            );

            if ($identificador === '') {
                $resultado['sin_documento']++;

                continue;
            }

            try {
                $preparacion = $this->prepararEstudiante(
                    $student->id,
                    $periodoDestinoId,
                    $fechaVencimiento,
                    $usuarioAdministrador
                );

                if (
                    ($preparacion['accion'] ?? null)
                    === 'omitido_existente'
                ) {
                    $resultado['pre_matriculas_omitidas']++;

                    continue;
                }

                if (
                    $preparacion['accion_usuario']
                    === 'creado'
                ) {
                    $resultado['usuarios_creados']++;
                }

                if (
                    $preparacion['accion_usuario']
                    === 'reutilizado'
                ) {
                    $resultado['usuarios_reutilizados']++;
                }

                $resultado['pre_matriculas_creadas']++;

                $resultado['documentos_encontrados'] +=
                    (int) ($preparacion['documentos_encontrados'] ?? 0);

                $resultado['documentos_copiados'] +=
                    (int) ($preparacion['documentos_copiados'] ?? 0);

                $resultado['documentos_ya_existentes'] +=
                    (int) ($preparacion['documentos_ya_existentes'] ?? 0);
            } catch (ValidationException $exception) {
                $resultado['conflictos']++;

                $resultado['detalle_conflictos'][] = [
                    'student_id' => $student->id,
                    'documento' => $identificador,
                    'nombre' => trim(
                        (string) $student->nombres
                        . ' '
                        . (string) $student->apellidos
                    ),
                    'mensaje' => collect(
                        $exception->errors()
                    )
                        ->flatten()
                        ->first()
                        ?? $exception->getMessage(),
                ];
            } catch (\Throwable $exception) {
                $resultado['errores']++;

                $resultado['detalle_errores'][] = [
                    'student_id' => $student->id,
                    'documento' => $identificador,
                    'nombre' => trim(
                        (string) $student->nombres
                        . ' '
                        . (string) $student->apellidos
                    ),
                    'mensaje' => $exception->getMessage(),
                ];
            }
        }

        return $resultado;
    }

    public function prepararEstudiante(
        int $studentId,
        int $periodoDestinoId,
        string $fechaVencimiento,
        User $usuarioAdministrador
    ): array {
        return DB::transaction(function () use (
            $studentId,
            $periodoDestinoId,
            $fechaVencimiento,
            $usuarioAdministrador
        ) {
            $student = Student::query()
                ->findOrFail($studentId);

            $periodoDestino = PeriodoLectivo::query()
                ->findOrFail($periodoDestinoId);

            if ((int) $periodoDestino->sede_id !== (int) $student->sede_id) {
                throw ValidationException::withMessages([
                    'periodo_destino_id' => 'El periodo de destino no pertenece a la misma sede del estudiante.',
                ]);
            }

            $identificador = $this->normalizarIdentificador(
                $student->documento
            );

            $preMatriculaExistente = PreMatricula::query()
                ->where('sede_id', $student->sede_id)
                ->where('periodo_destino_id', $periodoDestino->id)
                ->whereRaw(
                    'UPPER(TRIM(documento)) = ?',
                    [$identificador]
                )
                ->latest('id')
                ->first();

            if ($preMatriculaExistente) {
                return [
                    'student_id' => $student->id,
                    'usuario_id' => $preMatriculaExistente->user_id,
                    'accion_usuario' => 'sin_cambios',
                    'pre_matricula_id' => $preMatriculaExistente->id,
                    'numero_formulario' => $preMatriculaExistente->numero_formulario,
                    'codigo' => $preMatriculaExistente->codigo,
                    'periodo_origen_id' => $preMatriculaExistente->periodo_lectivo_id,
                    'periodo_destino_id' => $preMatriculaExistente->periodo_destino_id,
                    'estado' => $preMatriculaExistente->estado,
                    'accion' => 'omitido_existente',
                ];
            }
            $usuarioResultado = $this->resolverUsuarioTemporalEstudiante(
                $student->id,
                (int) $student->sede_id,
                $fechaVencimiento
            );

            /** @var User $usuarioTemporal */
            $usuarioTemporal = $usuarioResultado['usuario'];

            $snapshot = $this->prepararSnapshotEstudiante(
                $student->id
            );

            $datos = $snapshot['datos_pre_matricula'];

            $preMatricula = PreMatricula::create(array_merge(
                $datos,
                [
                    'numero_formulario' => null,
                    'user_id' => $usuarioTemporal->id,
                    'sede_id' => $student->sede_id,
                    'periodo_lectivo_id' => $student->periodo_lectivo_id,
                    'periodo_destino_id' => $periodoDestino->id,
                    'estado' => 'pendiente',
                    'fecha_habilitacion' => now(),
                    'fecha_limite' => $fechaVencimiento,
                    'creado_por' => $usuarioAdministrador->id,
                    'actualizado_por' => $usuarioAdministrador->id,
                ]
            ));

            $numero = (int) $preMatricula->id;

            do {
                $numeroFormulario = 'PRE-' . $numero;

                $existe = PreMatricula::query()
                    ->where('numero_formulario', $numeroFormulario)
                    ->where('id', '!=', $preMatricula->id)
                    ->exists();

                if ($existe) {
                    $numero++;
                }
            } while ($existe);

            $preMatricula->forceFill([
                'numero_formulario' => $numeroFormulario,
            ])->save();

            /*
            |--------------------------------------------------------------------------
            | Copiar expediente documental a la nueva pre-matrícula
            |--------------------------------------------------------------------------
            |
            | Los documentos existentes del estudiante se COPIAN.
            | El expediente original permanece intacto.
            |
            | Si el estudiante no tiene documentos, simplemente devuelve cero.
            |
            */

            $documentosResultado = app(
                CopiarDocumentosEstudiantePreMatriculaService::class
            )->ejecutar(
                $student,
                $preMatricula
            );

            return [
                'student_id' => $student->id,
                'usuario_id' => $usuarioTemporal->id,
                'accion_usuario' => $usuarioResultado['accion'],
                'pre_matricula_id' => $preMatricula->id,
                'numero_formulario' => $preMatricula->numero_formulario,
                'codigo' => $preMatricula->codigo,
                'periodo_origen_id' => $preMatricula->periodo_lectivo_id,
                'periodo_destino_id' => $preMatricula->periodo_destino_id,
                'estado' => $preMatricula->estado,

                'documentos_encontrados' =>
                    $documentosResultado['documentos_encontrados'],

                'documentos_copiados' =>
                    $documentosResultado['documentos_copiados'],

                'documentos_ya_existentes' =>
                    $documentosResultado['documentos_ya_existentes'],
            ];
        });
    }
    public function prepararSnapshotEstudiante(
        int $studentId
    ): array {
        $student = Student::query()
            ->with([
                'course',
                'guardians' => function ($query) {
                    $query->where(
                        'guardian_student.estado',
                        'activo'
                    );
                },
            ])
            ->findOrFail($studentId);

        $datos = [
            /*
            |--------------------------------------------------------------------------
            | Identificación del estudiante
            |--------------------------------------------------------------------------
            */
            'nombres' => $this->normalizarTexto(
                $student->nombres
            ),

            'apellidos' => $this->normalizarTexto(
                $student->apellidos
            ),

            'tipo_documento' => $this->normalizarTexto(
                $student->tipo_documento
            ),

            'documento' => $this->normalizarIdentificador(
                $student->documento
            ),

            'codigo' => trim(
                (string) $student->codigo
            ),

            /*
            |--------------------------------------------------------------------------
            | Datos personales disponibles
            |--------------------------------------------------------------------------
            */
            'ciudad_expedicion' => $this->normalizarTexto(
                $student->ciudad_expedicion
            ),

            'fecha_nacimiento' =>
                $student->fecha_nacimiento,

            'edad' => $student->edad,

            'ciudad_nacimiento' => $this->normalizarTexto(
                $student->ciudad_nacimiento
            ),

            'genero' => $this->normalizarTexto(
                $student->sexo
            ),

            'numero_hermanos' =>
                $student->numero_hermanos,

            /*
            |--------------------------------------------------------------------------
            | Student actualmente no posee teléfono/dirección personal
            |--------------------------------------------------------------------------
            */
            'telefono' => null,

            'correo' => $this->normalizarCorreo(
                $student->correo
            ),

            'direccion' => null,

            'rh' => $this->normalizarTexto(
                $student->rh
            ),

            'telefono_emergencia' =>
                $this->normalizarTexto(
                    $student->telefono_emergencia
                ),

            'institucion_anterior' =>
                $this->normalizarTexto(
                    $student->institucion_anterior
                ),

            /*
            |--------------------------------------------------------------------------
            | Estos campos se completarán/confirmarán en el formulario
            |--------------------------------------------------------------------------
            */
            'grado_aspira' => null,
            'condicion_ingreso' => null,

            /*
            |--------------------------------------------------------------------------
            | Responsables
            |--------------------------------------------------------------------------
            */
            'padre_nombre' => null,
            'padre_telefono' => null,
            'padre_tipo_documento' => null,
            'padre_documento' => null,
            'padre_lugar_trabajo' => null,
            'padre_correo' => null,
            'padre_direccion' => null,

            'madre_nombre' => null,
            'madre_telefono' => null,
            'madre_tipo_documento' => null,
            'madre_documento' => null,
            'madre_lugar_trabajo' => null,
            'madre_correo' => null,
            'madre_direccion' => null,

            'acudiente_origen' => null,
            'acudiente_parentesco' => null,
            'acudiente_nombre' => null,
            'acudiente_telefono' => null,
            'acudiente_tipo_documento' => null,
            'acudiente_documento' => null,
            'acudiente_lugar_trabajo' => null,
            'acudiente_correo' => null,
            'acudiente_direccion' => null,
        ];

        /*
        |--------------------------------------------------------------------------
        | EPS
        |--------------------------------------------------------------------------
        |
        | Student conserva actualmente la EPS como texto.
        | PreMatricula utiliza eps_id y eps_otro.
        |
        | Si encontramos la EPS del catálogo, usamos eps_id.
        | Si no existe, conservamos el texto en eps_otro.
        |
        */

        $eps = $this->resolverEps(
            $student->eps
        );

        $datos['eps_id'] = $eps['eps_id'];
        $datos['eps_otro'] = $eps['eps_otro'];

        /*
        |--------------------------------------------------------------------------
        | Responsables actuales
        |--------------------------------------------------------------------------
        |
        | Únicamente usamos las relaciones activas de guardian_student.
        | Si el estudiante no tiene responsables, los campos permanecen vacíos.
        |
        */

        foreach ($student->guardians as $guardian) {
            $tipo = mb_strtolower(
                trim((string) $guardian->pivot->tipo),
                'UTF-8'
            );

            if ($tipo === 'padre') {
                $this->copiarPadre(
                    $datos,
                    $guardian
                );

                continue;
            }

            if ($tipo === 'madre') {
                $this->copiarMadre(
                    $datos,
                    $guardian
                );

                continue;
            }

            if ($tipo === 'acudiente') {
                $this->copiarAcudiente(
                    $datos,
                    $guardian
                );
            }
        }

        return [
            'student_id' => $student->id,

            'datos_pre_matricula' => $datos,

            'referencia' => [
                'grado_actual' =>
                    $student->course?->grado
                    ?? $student->ultimo_grado,

                'responsables_encontrados' =>
                    $student->guardians->count(),
            ],
        ];
    }

    private function copiarPadre(
        array &$datos,
        Guardian $guardian
    ): void {
        $datos['padre_nombre'] =
            $this->normalizarTexto($guardian->nombre);

        $datos['padre_telefono'] =
            $this->normalizarTexto($guardian->telefono);

        $datos['padre_tipo_documento'] =
            $this->normalizarTexto(
                $guardian->tipo_documento
            );

        $datos['padre_documento'] =
            $this->normalizarIdentificador(
                $guardian->documento
            );

        $datos['padre_lugar_trabajo'] =
            $this->normalizarTexto(
                $guardian->lugar_trabajo
            );

        $datos['padre_correo'] =
            $this->normalizarCorreo(
                $guardian->correo
            );

        $datos['padre_direccion'] =
            $this->normalizarTexto(
                $guardian->direccion
            );
    }

    private function copiarMadre(
        array &$datos,
        Guardian $guardian
    ): void {
        $datos['madre_nombre'] =
            $this->normalizarTexto($guardian->nombre);

        $datos['madre_telefono'] =
            $this->normalizarTexto($guardian->telefono);

        $datos['madre_tipo_documento'] =
            $this->normalizarTexto(
                $guardian->tipo_documento
            );

        $datos['madre_documento'] =
            $this->normalizarIdentificador(
                $guardian->documento
            );

        $datos['madre_lugar_trabajo'] =
            $this->normalizarTexto(
                $guardian->lugar_trabajo
            );

        $datos['madre_correo'] =
            $this->normalizarCorreo(
                $guardian->correo
            );

        $datos['madre_direccion'] =
            $this->normalizarTexto(
                $guardian->direccion
            );
    }

    private function copiarAcudiente(
        array &$datos,
        Guardian $guardian
    ): void {
        $datos['acudiente_parentesco'] =
            $this->normalizarTexto(
                $guardian->parentesco
            );

        $datos['acudiente_nombre'] =
            $this->normalizarTexto(
                $guardian->nombre
            );

        $datos['acudiente_telefono'] =
            $this->normalizarTexto(
                $guardian->telefono
            );

        $datos['acudiente_tipo_documento'] =
            $this->normalizarTexto(
                $guardian->tipo_documento
            );

        $datos['acudiente_documento'] =
            $this->normalizarIdentificador(
                $guardian->documento
            );

        $datos['acudiente_lugar_trabajo'] =
            $this->normalizarTexto(
                $guardian->lugar_trabajo
            );

        $datos['acudiente_correo'] =
            $this->normalizarCorreo(
                $guardian->correo
            );

        $datos['acudiente_direccion'] =
            $this->normalizarTexto(
                $guardian->direccion
            );
    }

    private function resolverEps(
        mixed $valor
    ): array {
        $nombre = trim((string) $valor);

        if ($nombre === '') {
            return [
                'eps_id' => null,
                'eps_otro' => null,
            ];
        }

        $eps = Eps::query()
            ->whereRaw(
                'UPPER(TRIM(nombre)) = ?',
                [
                    mb_strtoupper(
                        $nombre,
                        'UTF-8'
                    ),
                ]
            )
            ->first();

        if ($eps) {
            return [
                'eps_id' => $eps->id,
                'eps_otro' => null,
            ];
        }

        return [
            'eps_id' => null,
            'eps_otro' =>
                $this->normalizarTexto($nombre),
        ];
    }

    private function normalizarTexto(
        mixed $valor
    ): ?string {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        return mb_strtoupper(
            $valor,
            'UTF-8'
        );
    }

    private function normalizarCorreo(
        mixed $valor
    ): ?string {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        return mb_strtolower(
            $valor,
            'UTF-8'
        );
    }

    private function normalizarIdentificador(
        mixed $valor
    ): string {
        return mb_strtoupper(
            trim((string) $valor),
            'UTF-8'
        );
    }
}