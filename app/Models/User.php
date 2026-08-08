<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'correo',
        'password',
        'is_active',
        'expires_at',
        'sede_id',
        'tipo_usuario',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function sedes()
    {
        return $this->belongsToMany(Sede::class, 'sede_user');
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['superadmin', 'admin']);
    }

    public function canAccessSede(int $sedeId): bool
    {
        if ($this->isAdmin()) return true;
        return $this->sedes()->where('sedes.id', $sedeId)->exists();
    }


    public function docente()
    {
        return $this->hasOne(Docente::class);
    }

    public function guardians()
    {
        return $this->hasMany(Guardian::class);
    }

    public function operacionesPagoRegistradas() { return $this->hasMany(OperacionPago::class, 'registrado_por'); }
    public function operacionesPagoAnuladas() { return $this->hasMany(OperacionPago::class, 'anulado_por'); }
    public function recibosRecibidos() { return $this->hasMany(ReciboPago::class, 'recibido_por'); }
    public function recibosAnulados() { return $this->hasMany(ReciboPago::class, 'anulado_por'); }
    public function movimientosSaldoFavorRegistrados() { return $this->hasMany(MovimientoSaldoFavor::class, 'registrado_por'); }
    public function acuerdosPagoRegistrados() { return $this->hasMany(AcuerdoPagoEstudiante::class, 'registrado_por'); }
    public function acuerdosPagoAnulados() { return $this->hasMany(AcuerdoPagoEstudiante::class, 'anulado_por'); }
    public function evidenciasAcuerdoCargadas() { return $this->hasMany(EvidenciaAcuerdoPago::class, 'cargado_por'); }
    public function impresionesRecibos() { return $this->hasMany(ImpresionRecibo::class, 'generado_por'); }
    public function extractosGenerados() { return $this->hasMany(ExtractoEstudiante::class, 'generado_por'); }

    public function preMatricula()
    {
        return $this->hasOne(PreMatricula::class, 'user_id');
    }

    public function preMatriculasCreadas()
    {
        return $this->hasMany(PreMatricula::class, 'creado_por');
    }

    public function preMatriculasActualizadas()
    {
        return $this->hasMany(PreMatricula::class, 'actualizado_por');
    }

    public function historialPreMatriculas()
    {
        return $this->hasMany(
            PreMatriculaHistorial::class,
            'user_id'
        );
    }

}