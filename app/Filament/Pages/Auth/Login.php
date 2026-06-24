<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use App\Models\Sede;
use Filament\Forms\Form;
use Filament\Forms\Components\Component;
use App\Models\PeriodoLectivo;

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
        session([
            'sede_id' => $this->sede_id,
            'anio'    => $this->anio,
        ]);

        return parent::authenticate();
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
}