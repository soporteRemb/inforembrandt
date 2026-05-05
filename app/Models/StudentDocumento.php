<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDocumento extends Model
{
    protected $table = 'student_documentos';

    protected $fillable = ['student_codigo', 'tipo', 'generado_at', 'generado_por'];

    protected $casts = ['generado_at' => 'datetime'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_codigo', 'codigo');
    }

    // Lista canónica de todos los documentos del proceso de matrícula
    public static function todos(): array
    {
        return [
            'comportamiento'        => ['label' => 'Formato comportamiento',                 'color' => '#fef9c3', 'text' => '#854d0e', 'border' => '#fde047', 'pdf' => false],
            'pagare'                => ['label' => 'Pagaré GA-F09',                          'color' => '#f3e8ff', 'text' => '#6b21a8', 'border' => '#d8b4fe', 'pdf' => false],
            'autorizacion_consulta' => ['label' => 'Autorización consulta y reporte GA-F10', 'color' => '#ffe4e6', 'text' => '#9f1239', 'border' => '#fda4af', 'pdf' => false],
            'autorizacion_pagare'   => ['label' => 'Autorización uso de pagaré GA-F13',      'color' => '#ffedd5', 'text' => '#9a3412', 'border' => '#fdba74', 'pdf' => false],
            'autorizacion_datos'    => ['label' => 'Autorización datos personales GA-F14',   'color' => '#e0f2fe', 'text' => '#075985', 'border' => '#7dd3fc', 'pdf' => false],
            'contrato_servicios'    => ['label' => 'Contrato de servicios',                  'color' => '#f1f5f9', 'text' => '#1e293b', 'border' => '#94a3b8', 'pdf' => false],
        ];
    }
}
