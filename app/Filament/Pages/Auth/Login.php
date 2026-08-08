<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Form;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;

use App\Models\PeriodoLectivo;
use App\Models\Sede;
use App\Models\User;
use App\Models\PreMatricula;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    public string $sede_id = '';
    public string $anio = '';

    public function mount(): void
    {
        parent::mount();
        $this->anio = PeriodoLectivo::query()
            ->orderByDesc('nombre')
            ->value('nombre') ?? date('Y');
        $sede = Sede::where('activa', true)->first();
        $this->sede_id = $sede ? (string) $sede->id : '';
    }

    public function getSedes()
    {
        return Sede::where('activa', true)->pluck('nombre', 'id')->toArray();
    }

    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Mantener el contexto académico seleccionado
        |--------------------------------------------------------------------------
        |
        | Esta lógica ya existe y debe conservarse:
        | - sede seleccionada;
        | - año o período lectivo seleccionado.
        |
        */
        session([
            'sede_id' => $this->sede_id,
            'anio'    => $this->anio,
        ]);

        $datos = $this->form->getState();

        $usuario = User::query()
            ->where('email', $datos['email'] ?? '')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Credenciales incorrectas
        |--------------------------------------------------------------------------
        |
        | Dejamos que Filament procese normalmente los errores de usuario
        | inexistente o contraseña incorrecta.
        |
        */
        if (
            ! $usuario ||
            ! Hash::check($datos['password'] ?? '', $usuario->password)
        ) {
            return parent::authenticate();
        }

        /*
        |--------------------------------------------------------------------------
        | Superadministrador
        |--------------------------------------------------------------------------
        |
        | El superadmin no se bloquea por fecha de vencimiento.
        |
        */
        if ($usuario->hasRole('superadmin')) {
            return parent::authenticate();
        }

        /*
        |--------------------------------------------------------------------------
        | Fecha vencida
        |--------------------------------------------------------------------------
        |
        | Cuando existe una fecha y ya terminó, se desactiva la cuenta.
        |
        | Por ahora, los usuarios antiguos que aún tengan expires_at = null
        | podrán seguir entrando hasta que se les asigne una fecha.
        |
        */
        if (
            $usuario->expires_at &&
            now()->greaterThan($usuario->expires_at)
        ) {
            $usuario->forceFill([
                'is_active' => false,
            ])->save();
        }

        /*
        |--------------------------------------------------------------------------
        | Usuario inactivo o vencido
        |--------------------------------------------------------------------------
        */
        if (! $usuario->is_active) {
            throw ValidationException::withMessages([
                'data.email' => 'Su cuenta no se encuentra habilitada. Comuníquese con la institución educativa.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar sede y período del usuario temporal
        |--------------------------------------------------------------------------
        */
        if ($usuario->hasRole('temporal')) {
            $preMatricula = PreMatricula::query()
                ->with('periodoLectivo')
                ->where('user_id', $usuario->id)
                ->latest('id')
                ->first();

            if (! $preMatricula) {
                throw ValidationException::withMessages([
                    'data.email' => 'No se encontró un formulario habilitado para esta cuenta.',
                ]);
            }

            $sedeSeleccionada = (int) $this->sede_id;
            $anioSeleccionado = (string) $this->anio;

            $sedeHabilitada = (int) $preMatricula->sede_id;
            $anioHabilitado = (string) (
                $preMatricula->periodoLectivo?->nombre ?? ''
            );

            if (
                $sedeSeleccionada !== $sedeHabilitada
                || $anioSeleccionado !== $anioHabilitado
            ) {
                throw ValidationException::withMessages([
                    'data.email' => 'La sede o el período seleccionado no corresponde al acceso habilitado para esta cuenta.',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Autenticar normalmente
        |--------------------------------------------------------------------------
        */
        $response = parent::authenticate();

        /*
        |--------------------------------------------------------------------------
        | Redirección exclusiva para el rol temporal
        |--------------------------------------------------------------------------
        */
        $usuarioAutenticado = auth()->user();

        if ($usuarioAutenticado?->hasRole('temporal')) {
            return new class implements
                \Filament\Http\Responses\Auth\Contracts\LoginResponse
            {
                public function toResponse($request)
                {
                    return redirect()->to(
                        \App\Filament\Pages\FormularioPreMatricula::getUrl()
                    );
                }
            };
        }

        return $response;
    }
    public function form(Form $form): Form
    {
        return $form->schema([
            $this->getEmailFormComponent()
                ->label('Usuario')
                ->placeholder('Usuario')
                ->prefixIcon('heroicon-o-user'),
            $this->getPasswordFormComponent()
                ->label('Contraseña')
                ->placeholder('Contraseña')
                ->prefixIcon('heroicon-o-lock-closed'),
        ]);
    }

    protected function getAuthenticateFormAction(): \Filament\Actions\Action
    {
        return parent::getAuthenticateFormAction()->label('Iniciar Sesión');
    }

    public function getAnios()
    {
        return PeriodoLectivo::query()
            ->select('nombre')
            ->distinct()
            ->orderByDesc('nombre')
            ->pluck('nombre')
            ->values()
            ->toArray();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Usuario')
            ->placeholder('Usuario')
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->prefixIcon('heroicon-o-user');
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => 'Usuario o contraseña incorrectos.',
        ]);
    }
}