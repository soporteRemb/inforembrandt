<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    protected $fillable = [
        'student_id',
        'tipo',
        'nombre',
        'tipo_documento',
        'documento',
        'telefono',
        'correo',
        'direccion',
        'lugar_trabajo',
        'parentesco',
        'estado',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}