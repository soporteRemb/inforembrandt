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
        'password',
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

}