<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;

use App\Services\PreMatriculas\PreMatriculaFormularioService;

use App\Models\PreMatricula;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FormularioPreMatricula extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Formulario';

    protected static ?string $title = 'Formulario de pre-matrícula';

    protected static ?string $slug = 'formulario-pre-matricula';

    protected static string $view =
        'filament.pages.formulario-pre-matricula';


        /*
    |--------------------------------------------------------------------------
    | esta es la pagina que ve la persona que va a llenar el formulario, el usuario temporal
    |--------------------------------------------------------------------------
    |
    | 
    |
    */

    /*
    |--------------------------------------------------------------------------
    | No mostrar en el menú administrativo
    |--------------------------------------------------------------------------
    |
    | El usuario temporal llegará directamente a esta ruta.
    | Durante el diseño, administradores y superadmin podrán abrirla
    | manualmente para revisar la interfaz.
    |
    */
    protected static bool $shouldRegisterNavigation = false;

    /*
    |--------------------------------------------------------------------------
    | Datos del estudiante
    |--------------------------------------------------------------------------
    */

    public string $nombres = '';

    public string $apellidos = '';

    public string $tipo_documento = '';

    public string $documento = '';

    public string $ciudad_expedicion = '';

    public ?string $fecha_nacimiento = null;

    public ?int $edad = null;

    public string $ciudad_nacimiento = '';

    public string $genero = '';

    public int|string $numero_hermanos = 0;

    public string $telefono = '';

    public string $correo = '';

    public string $direccion = '';

    public string $rh = '';

    public string $eps_id = '';

    public ?string $eps_otro = null;

    public string $telefono_emergencia = '';

    public string $grado_aspira = '';

    public string $institucion_anterior = '';

    public string $condicion_ingreso = '';

    public ?int $preMatriculaId = null;

    /*
    |--------------------------------------------------------------------------
    | Padre
    |--------------------------------------------------------------------------
    */

    public string $padre_nombre = '';

    public string $padre_telefono = '';

    public string $padre_tipo_documento = '';

    public string $padre_documento = '';

    public string $padre_lugar_trabajo = '';

    public string $padre_correo = '';

    public string $padre_direccion = '';

    /*
    |--------------------------------------------------------------------------
    | Madre
    |--------------------------------------------------------------------------
    */

    public string $madre_nombre = '';

    public string $madre_telefono = '';

    public string $madre_tipo_documento = '';

    public string $madre_documento = '';

    public string $madre_lugar_trabajo = '';

    public string $madre_correo = '';

    public string $madre_direccion = '';

    /*
    |--------------------------------------------------------------------------
    | Acudiente
    |--------------------------------------------------------------------------
    */

    public string $acudiente_origen = 'otro';

    public string $acudiente_parentesco = '';

    public string $acudiente_nombre = '';

    public string $acudiente_telefono = '';

    public string $acudiente_tipo_documento = '';

    public string $acudiente_documento = '';

    public string $acudiente_lugar_trabajo = '';

    public string $acudiente_correo = '';

    public string $acudiente_direccion = '';

    /*
    |--------------------------------------------------------------------------
    | Modal
    |--------------------------------------------------------------------------
    */

    public bool $mostrarModalConfirmacion = false;

    

    /*
    |--------------------------------------------------------------------------
    | Catálogos visuales temporales
    |--------------------------------------------------------------------------
    */

    public array $tiposDocumento = [
        'TI' => 'Tarjeta de identidad',
        'RC' => 'Registro civil',
        'CC' => 'Cédula de ciudadanía',
        'CE' => 'Cédula de extranjería',
        'PAS' => 'Pasaporte',
        'PPT' => 'Permiso por protección temporal',
    ];

    public array $grados = [];

    public array $eps = [];

    public array $rhOpciones = [
        'O+',
        'O-',
        'A+',
        'A-',
        'B+',
        'B-',
        'AB+',
        'AB-',
    ];

    public function getHeading(): string
    {
        return '';
    }

    /*
    |--------------------------------------------------------------------------
    | Edad calculada visualmente
    |--------------------------------------------------------------------------
    */

    public function updatedFechaNacimiento(
        ?string $fechaNacimiento
    ): void {
        $this->edad = app(
            PreMatriculaFormularioService::class
        )->calcularEdad($fechaNacimiento);
    }

    public function updatedEpsId(): void
    {
        if (! $this->esEpsOtro()) {
            $this->eps_otro = null;
            $this->resetValidation('eps_otro');
        }
    }

    public function esEpsOtro(): bool
    {
        $nombreEps = $this->eps[$this->eps_id] ?? '';

        return mb_strtolower(
            trim((string) $nombreEps),
            'UTF-8'
        ) === 'otro';
    }

    /*
    |--------------------------------------------------------------------------
    | Origen del acudiente
    |--------------------------------------------------------------------------
    */

    public function seleccionarOrigenAcudiente(string $origen): void
    {
        if (! in_array($origen, ['padre', 'madre', 'otro'], true)) {
            return;
        }

        $this->acudiente_origen = $origen;

        match ($origen) {
            'padre' => $this->copiarPadreComoAcudiente(),
            'madre' => $this->copiarMadreComoAcudiente(),
            'otro' => $this->limpiarAcudiente(),
        };
    }

    private function copiarPadreComoAcudiente(): void
    {
        $this->acudiente_parentesco = 'Padre';
        $this->acudiente_nombre = $this->padre_nombre;
        $this->acudiente_telefono = $this->padre_telefono;
        $this->acudiente_tipo_documento =
            $this->padre_tipo_documento;
        $this->acudiente_documento = $this->padre_documento;
        $this->acudiente_lugar_trabajo =
            $this->padre_lugar_trabajo;
        $this->acudiente_correo = $this->padre_correo;
        $this->acudiente_direccion = $this->padre_direccion;
    }

    private function copiarMadreComoAcudiente(): void
    {
        $this->acudiente_parentesco = 'Madre';
        $this->acudiente_nombre = $this->madre_nombre;
        $this->acudiente_telefono = $this->madre_telefono;
        $this->acudiente_tipo_documento =
            $this->madre_tipo_documento;
        $this->acudiente_documento = $this->madre_documento;
        $this->acudiente_lugar_trabajo =
            $this->madre_lugar_trabajo;
        $this->acudiente_correo = $this->madre_correo;
        $this->acudiente_direccion = $this->madre_direccion;
    }

    private function limpiarAcudiente(): void
    {
        $this->acudiente_parentesco = '';
        $this->acudiente_nombre = '';
        $this->acudiente_telefono = '';
        $this->acudiente_tipo_documento = '';
        $this->acudiente_documento = '';
        $this->acudiente_lugar_trabajo = '';
        $this->acudiente_correo = '';
        $this->acudiente_direccion = '';
    }

    /*
    |--------------------------------------------------------------------------
    | Modal visual
    |--------------------------------------------------------------------------
    */

    public function abrirConfirmacion(): void
    {
        $this->resetErrorBag();

        $this->sincronizarAcudienteSeleccionado();

        $this->validate();

        if (! $this->validarCamposResponsablesIniciados()) {
            return;
        }

        if (! $this->tieneResponsableCompleto()) {
            $this->addError(
                'responsable',
                'Debe registrar al menos un responsable del estudiante: padre, madre o acudiente.'
            );

            return;
        }

        $this->mostrarModalConfirmacion = true;
    }

    public function cancelarEnvio(): void
    {
        $this->mostrarModalConfirmacion = false;
    }

    public function confirmarEnvio(): mixed
    {
        $usuario = auth()->user();

        if (! $usuario) {
            $this->mostrarModalConfirmacion = false;

            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Evitar guardado desde la vista administrativa
        |--------------------------------------------------------------------------
        */
        if ($usuario->hasAnyRole(['superadmin', 'admin'])) {
            $this->mostrarModalConfirmacion = false;

            Notification::make()
                ->title('Vista administrativa')
                ->body(
                    'Esta vista permite revisar el diseño, pero no enviar formularios.'
                )
                ->warning()
                ->send();

            return null;
        }

        if (! $this->preMatriculaId) {
            $this->mostrarModalConfirmacion = false;

            $this->addError(
                'formulario',
                'No se encontró el formulario que debe enviarse.'
            );

            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Última validación antes de guardar
        |--------------------------------------------------------------------------
        */
        $this->sincronizarAcudienteSeleccionado();

        try {
            $datos = $this->validate();
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->mostrarModalConfirmacion = false;

            throw $exception;
        }

        if (! $this->validarCamposResponsablesIniciados()) {
            $this->mostrarModalConfirmacion = false;

            return null;
        }

        if (! $this->tieneResponsableCompleto()) {
            $this->mostrarModalConfirmacion = false;

            $this->addError(
                'responsable',
                'Debe registrar al menos un responsable del estudiante: padre, madre o acudiente.'
            );

            return null;
        }

        $preMatricula = PreMatricula::query()
            ->whereKey($this->preMatriculaId)
            ->where('user_id', $usuario->id)
            ->first();

        if (! $preMatricula) {
            $this->mostrarModalConfirmacion = false;

            $this->addError(
                'formulario',
                'El formulario no pertenece a la cuenta autenticada.'
            );

            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Guardar formulario
        |--------------------------------------------------------------------------
        */
        app(PreMatriculaFormularioService::class)->enviar(
            $preMatricula,
            $datos,
            $usuario
        );

        /*
        |--------------------------------------------------------------------------
        | Cerrar sesión
        |--------------------------------------------------------------------------
        */
        $this->mostrarModalConfirmacion = false;

        /*
        |--------------------------------------------------------------------------
        | Cerrar la sesión del panel de Filament
        |--------------------------------------------------------------------------
        */
        filament()->auth()->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        /*
        |--------------------------------------------------------------------------
        | Regresar al inicio de sesión
        |--------------------------------------------------------------------------
        |
        | Como esta acción se ejecuta desde Livewire, usamos el redirect propio
        | del componente para que el navegador realice la navegación.
        |
        */
        return $this->redirect(
            filament()->getLoginUrl(),
            navigate: false
        );
    }

   

    /*
    |--------------------------------------------------------------------------
    | Acceso temporal durante la construcción
    |--------------------------------------------------------------------------
    */

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return false;
        }

        /*
        | Durante el diseño:
        | - superadmin y admin pueden revisar la pantalla;
        | - temporal podrá utilizarla.
        |
        | Después restringiremos definitivamente cada flujo.
        */
        return $usuario->hasAnyRole([
            'superadmin',
            'admin',
            'temporal',
        ]);
    }

    public function mount(
        PreMatriculaFormularioService $formularioService
    ): void {
        $this->grados = $formularioService->obtenerGrados();

        $this->eps = $formularioService
            ->obtenerEpsActivas()
            ->pluck('nombre', 'id')
            ->toArray();

        $usuario = auth()->user();

        if (! $usuario) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Vista administrativa
        |--------------------------------------------------------------------------
        |
        | Admin y superadmin pueden continuar entrando únicamente para revisar
        | el diseño. No se les asigna un formulario para guardar.
        |
        */
        if ($usuario->hasAnyRole(['superadmin', 'admin'])) {
            return;
        }

        $preMatricula = $formularioService
            ->obtenerFormularioUsuario($usuario);

        if (! $preMatricula) {
            throw ValidationException::withMessages([
                'formulario' =>
                    'No se encontró un formulario habilitado para esta cuenta.',
            ]);
        }

        $formularioService->verificarDisponibilidad(
            $usuario,
            $preMatricula
        );

        $this->preMatriculaId = $preMatricula->id;

        $this->fill(
            $formularioService->prepararDatosFormulario(
                $preMatricula
            )
        );
    }


    public function updatedPadreNombre(): void
    {
        $this->sincronizarPadreSiEsAcudiente();
    }

    public function updatedPadreTelefono(): void
    {
        $this->sincronizarPadreSiEsAcudiente();
    }

    public function updatedPadreTipoDocumento(): void
    {
        $this->sincronizarPadreSiEsAcudiente();
    }

    public function updatedPadreDocumento(): void
    {
        $this->sincronizarPadreSiEsAcudiente();
    }

    public function updatedPadreLugarTrabajo(): void
    {
        $this->sincronizarPadreSiEsAcudiente();
    }

    public function updatedPadreCorreo(): void
    {
        $this->sincronizarPadreSiEsAcudiente();
    }

    public function updatedPadreDireccion(): void
    {
        $this->sincronizarPadreSiEsAcudiente();
    }

    private function sincronizarPadreSiEsAcudiente(): void
    {
        if ($this->acudiente_origen === 'padre') {
            $this->copiarPadreComoAcudiente();
        }
    }


    public function updatedMadreNombre(): void
    {
        $this->sincronizarMadreSiEsAcudiente();
    }

    public function updatedMadreTelefono(): void
    {
        $this->sincronizarMadreSiEsAcudiente();
    }

    public function updatedMadreTipoDocumento(): void
    {
        $this->sincronizarMadreSiEsAcudiente();
    }

    public function updatedMadreDocumento(): void
    {
        $this->sincronizarMadreSiEsAcudiente();
    }

    public function updatedMadreLugarTrabajo(): void
    {
        $this->sincronizarMadreSiEsAcudiente();
    }

    public function updatedMadreCorreo(): void
    {
        $this->sincronizarMadreSiEsAcudiente();
    }

    public function updatedMadreDireccion(): void
    {
        $this->sincronizarMadreSiEsAcudiente();
    }

    private function sincronizarMadreSiEsAcudiente(): void
    {
        if ($this->acudiente_origen === 'madre') {
            $this->copiarMadreComoAcudiente();
        }
    }


    protected function rules(): array
    {
        $reglas = [

            // Estudiante
            'nombres' => ['required', 'string', 'max:120'],
            'apellidos' => ['required', 'string', 'max:120'],
            'tipo_documento' => ['required', 'string', 'max:10'],
            'documento' => ['required', 'string', 'max:30'],
            'ciudad_expedicion' => ['required', 'string', 'max:100'],

            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'ciudad_nacimiento' => ['required', 'string', 'max:100'],
            'genero' => ['required', 'string'],
            'numero_hermanos' => ['nullable', 'integer', 'min:0'],

            'telefono' => ['nullable', 'string', 'max:30'],
            'correo' => ['required', 'email', 'max:150'],
            'direccion' => ['required', 'string', 'max:180'],
            'rh' => ['required', 'string', 'max:5'],
            'eps_id' => ['required', 'exists:eps,id'],
            'eps_otro' => [
                $this->esEpsOtro() ? 'required' : 'nullable',
                'string',
                'max:180',
            ],

            'telefono_emergencia' => ['required', 'string', 'max:30'],
            'grado_aspira' => ['required', 'string', 'max:30'],
            'institucion_anterior' => ['required', 'string', 'max:180'],
            'condicion_ingreso' => ['required', 'string'],

            // Padre
            'padre_nombre' => ['nullable', 'string', 'max:180'],
            'padre_telefono' => ['nullable', 'string', 'max:40'],
            'padre_tipo_documento' => ['nullable', 'string', 'max:60'],
            'padre_documento' => ['nullable', 'string', 'max:50'],
            'padre_lugar_trabajo' => ['nullable', 'string', 'max:180'],
            'padre_correo' => ['nullable', 'email', 'max:180'],
            'padre_direccion' => ['nullable', 'string', 'max:255'],

            // Madre
            'madre_nombre' => ['nullable', 'string', 'max:180'],
            'madre_telefono' => ['nullable', 'string', 'max:40'],
            'madre_tipo_documento' => ['nullable', 'string', 'max:60'],
            'madre_documento' => ['nullable', 'string', 'max:50'],
            'madre_lugar_trabajo' => ['nullable', 'string', 'max:180'],
            'madre_correo' => ['nullable', 'email', 'max:180'],
            'madre_direccion' => ['nullable', 'string', 'max:255'],

            // Acudiente
            'acudiente_origen' => ['nullable', 'in:padre,madre,otro'],
            'acudiente_parentesco' => ['nullable', 'string', 'max:60'],
            'acudiente_nombre' => ['nullable', 'string', 'max:150'],
            'acudiente_telefono' => ['nullable', 'string', 'max:30'],
            'acudiente_tipo_documento' => ['nullable', 'string', 'max:30'],
            'acudiente_documento' => ['nullable', 'string', 'max:30'],
            'acudiente_lugar_trabajo' => ['nullable', 'string', 'max:150'],
            'acudiente_correo' => ['nullable', 'email', 'max:150'],
            'acudiente_direccion' => ['nullable', 'string', 'max:255'],
        ];

        return $reglas;
    }

    protected function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'email' => 'Ingrese un correo electrónico válido.',
            'date' => 'Ingrese una fecha válida.',
            'before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'integer' => 'Ingrese un número válido.',
            'min' => 'El valor ingresado no es válido.',
            'exists' => 'La opción seleccionada no es válida.',

            'acudiente_origen.in' => 'Seleccione quién será el acudiente.',
        ];
    }

    private function sincronizarAcudienteSeleccionado(): void
    {
        if ($this->acudiente_origen === 'padre') {
            $this->copiarPadreComoAcudiente();

            return;
        }

        if ($this->acudiente_origen === 'madre') {
            $this->copiarMadreComoAcudiente();
        }
    }


    private function tieneResponsableCompleto(): bool
    {
        $padreCompleto =
            filled($this->padre_nombre)
            && filled($this->padre_telefono)
            && filled($this->padre_tipo_documento)
            && filled($this->padre_documento);

        $madreCompleta =
            filled($this->madre_nombre)
            && filled($this->madre_telefono)
            && filled($this->madre_tipo_documento)
            && filled($this->madre_documento);

        $acudienteCompleto =
            filled($this->acudiente_nombre)
            && filled($this->acudiente_telefono)
            && filled($this->acudiente_tipo_documento)
            && filled($this->acudiente_documento);

        return $padreCompleto || $madreCompleta || $acudienteCompleto;
    }

    private function validarCamposResponsablesIniciados(): bool
    {
        $this->resetErrorBag([
            'padre_nombre',
            'padre_telefono',
            'padre_tipo_documento',
            'padre_documento',

            'madre_nombre',
            'madre_telefono',
            'madre_tipo_documento',
            'madre_documento',

            'acudiente_nombre',
            'acudiente_telefono',
            'acudiente_tipo_documento',
            'acudiente_documento',
        ]);

        $hayErrores = false;

        $grupos = [
            'padre' => [
                'padre_nombre',
                'padre_telefono',
                'padre_tipo_documento',
                'padre_documento',
            ],

            'madre' => [
                'madre_nombre',
                'madre_telefono',
                'madre_tipo_documento',
                'madre_documento',
            ],

            'acudiente' => [
                'acudiente_nombre',
                'acudiente_telefono',
                'acudiente_tipo_documento',
                'acudiente_documento',
            ],
        ];

        foreach ($grupos as $grupo => $camposObligatorios) {
            $camposParaDetectarInicio = match ($grupo) {
                'padre' => [
                    'padre_nombre',
                    'padre_telefono',
                    'padre_tipo_documento',
                    'padre_documento',
                    'padre_lugar_trabajo',
                    'padre_correo',
                    'padre_direccion',
                ],

                'madre' => [
                    'madre_nombre',
                    'madre_telefono',
                    'madre_tipo_documento',
                    'madre_documento',
                    'madre_lugar_trabajo',
                    'madre_correo',
                    'madre_direccion',
                ],

                'acudiente' => [
                    'acudiente_parentesco',
                    'acudiente_nombre',
                    'acudiente_telefono',
                    'acudiente_tipo_documento',
                    'acudiente_documento',
                    'acudiente_lugar_trabajo',
                    'acudiente_correo',
                    'acudiente_direccion',
                ],
            };

            $grupoIniciado = collect($camposParaDetectarInicio)
                ->contains(
                    fn (string $campo): bool =>
                        filled($this->{$campo} ?? null)
                );

            if (! $grupoIniciado) {
                continue;
            }

            foreach ($camposObligatorios as $campo) {
                if (blank($this->{$campo} ?? null)) {
                    $this->addError(
                        $campo,
                        'Este campo es obligatorio.'
                    );

                    $hayErrores = true;
                }
            }
        }

        return ! $hayErrores;
    }

}