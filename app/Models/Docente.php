<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    protected $table = 'docentes';

    protected $fillable = [
        'codigo',
        'user_id',
        'identificacion',
        'nombres',
        'apellidos',
        'telefono',
        'correo',
        'especialidad',
        'cargo',
        'estado',
        'direccion',
        'escalafon',
        'direccion_curso_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pensumAcademicos()
    {
        return $this->hasMany(PensumAcademico::class, 'docente_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }

    public function direccionCurso()
    {
        return $this->belongsTo(Course::class, 'direccion_curso_id');
    }

    public function asignaturas()
    {
        return $this->hasMany(DocenteAsignatura::class);
    }




}