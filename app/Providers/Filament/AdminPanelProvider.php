<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->locale('es')
            ->favicon(asset('images/Logo.png'))
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->brandName(function() {
                $sede = \App\Models\Sede::find(session('sede_id'));
                $anio = session('anio', date('Y'));
                $nombreSede = $sede ? $sede->nombre : 'Rembrandt';
                $logoUrl = asset('images/Logo.png');
                return new \Illuminate\Support\HtmlString(
                    '<span style="display:flex;align-items:center;gap:10px;">'
                    . '<img src="' . e($logoUrl) . '" style="height:36px;width:auto;flex-shrink:0;" alt="">'
                    . '<span>' . e($nombreSede . ' ' . $anio) . '</span>'
                    . '</span>'
                );
            })
            ->colors([
                'primary' => [
                    50  => '255, 241, 241',
                    100 => '255, 220, 220',
                    200 => '255, 180, 180',
                    300 => '255, 140, 140',
                    400 => '255, 100, 100',
                    500 => '220, 38, 38',
                    600 => '180, 20, 20',
                    700 => '143, 15, 18',
                    800 => '100, 10, 12',
                    900 => '70, 5, 8',
                    950 => '40, 2, 4',
                ],
            ])
            ->sidebarCollapsibleOnDesktop()
            ->renderHook('panels::user-menu.before', function() {
                $fecha = now()->locale('es_CO')->isoFormat('DD/MM/YYYY');
                return new \Illuminate\Support\HtmlString('
                    <div
                        x-data="{ hora: \'\' }"
                        x-init="
                            const actualizar = () => {
                                const n = new Date();
                                hora = n.toLocaleTimeString(\'es-CO\', { hour: \'2-digit\', minute: \'2-digit\', hour12: true });
                            };
                            actualizar();
                            setInterval(actualizar, 1000);
                        "
                        style="
                            display: flex;
                            align-items: center;
                            gap: 10px;
                            color: #1e293b;
                            font-size: 0.83rem;
                            font-weight: 500;
                            margin-right: 8px;
                            padding: 5px 13px;
                            background: #f1f5f9;
                            border: 1px solid #cbd5e1;
                            border-radius: 8px;
                            white-space: nowrap;
                        ">
                        <span style="display:flex;align-items:center;gap:5px;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg> ' . $fecha . '</span>
                        <span style="opacity:0.7;">|</span>
                        <span x-text="hora">--:--</span>
                    </div>
                ');
            })
            ->renderHook('panels::head.end', fn() => new \Illuminate\Support\HtmlString('
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
                <style>

                /* FUENTE GLOBAL */
                *, body, input, button, select, textarea {
                    font-family: \'Noto Sans\', sans-serif !important;
                }

                /* ALINEAR inputs verticalmente en el centro */
                .fi-input {
                    display: flex !important;
                    align-items: center !important;
                }

                /* Labels consistentes */
                .fi-fo-field-wrp-label {
                    min-height: 1.25rem;
                }

                /* TOPBAR ROJO */
                .fi-topbar,
                header.fi-topbar {
                    background-color: #82211d !important;
                    border-bottom: none !important;
                }

                /* Texto del brand en topbar */
                .fi-topbar .fi-brand-name {
                    color: white !important;
                    font-weight: 700;
                }

                /* Íconos directos en la barra (search, bell, toggle sidebar) — NO dropdowns */
                .fi-topbar > nav > button svg,
                .fi-topbar > nav > a svg,
                .fi-topbar > nav > div:not([role="dialog"]):not(.fi-dropdown-panel) > button:not(.fi-dropdown-item) svg {
                    color: white !important;
                }
                .fi-topbar > nav > button:hover,
                .fi-topbar > nav > a:hover {
                    background: rgba(255,255,255,0.15) !important;
                }

                /* TÍTULOS DE SECCIÓN — oscuros (como estaban) */
                .fi-section-header-heading {
                    color: #1e293b !important;
                    font-size: 0.95rem !important;
                    font-weight: 600 !important;
                }

                /* LABELS DE CAMPOS — gris suave (el texto está en el span interno) */
                .fi-fo-field-wrp-label span {
                    color: #64748b !important;
                }

                /* FOTO AVATAR — centrar dentro de su columna */
                .fi-fo-file-upload .fi-fo-avatar {
                    margin: 0 auto !important;
                    display: block !important;
                }
                .fi-fo-file-upload {
                    display: flex !important;
                    justify-content: center !important;
                }

                /* SIDEBAR ROJO */
                .fi-sidebar,
                aside.fi-sidebar {
                    background-color: #82211d !important;
                }

                /* TEXTO NORMAL (VISIBLE 🔥) */
                .fi-sidebar-item-button {
                    color: rgba(255,255,255,0.85) !important;
                }

                /* ICONOS NORMALES */
                .fi-sidebar-item-button svg {
                    color: rgba(255,255,255,0.85) !important;
                }

                /* HOVER */
                .fi-sidebar-item-button:hover {
                    background-color: rgba(255,255,255,0.15) !important;
                    color: #ffffff !important;
                }

                /* ITEM ACTIVO */
                .fi-sidebar-item-active {
                    background-color: #ffffff !important;
                    border-radius: 10px;
                }

                /* TEXTO ACTIVO */
                .fi-sidebar-item-active .fi-sidebar-item-label {
                    color: #82211d !important;
                    font-weight: 700;
                }

                /* ICONO ACTIVO */
                .fi-sidebar-item-active svg {
                    color: #82211d !important;
                }

                /* GRUPOS */
                .fi-sidebar-group-label {
                    color: rgba(255,255,255,0.6) !important;
                }
                /* TODO lo normal */
                .fi-sidebar-item-label {
                    color: rgba(255,255,255,0.7) !important;
                }

                /* SOLO EL ACTIVO LO SOBRESCRIBE */
                .fi-sidebar-item-active .fi-sidebar-item-label {
                    color: #82211d !important;
                }
                </style>
                '))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}