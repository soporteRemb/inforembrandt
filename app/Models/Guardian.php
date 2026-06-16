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
        'user_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}