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
            'comportamiento'        => ['label' => 'Formato comportamiento',                 'color' => '#fef9c3', 'text' => '#854d0e', 'border' => '#fde047', 'archivo' => 'FORMATO COMPORTAMIENTO.pdf'],
            'pagare'                => ['label' => 'Pagaré GA-F09',                          'color' => '#f3e8ff', 'text' => '#6b21a8', 'border' => '#d8b4fe', 'archivo' => 'GA-F09 Pagaré V.4 2026.pdf'],
            'autorizacion_consulta' => ['label' => 'Autorización consulta y reporte GA-F10', 'color' => '#ffe4e6', 'text' => '#9f1239', 'border' => '#fda4af', 'archivo' => 'GA-F10 Autorización consulta y reporte V.2 2024.pdf'],
            'autorizacion_pagare'   => ['label' => 'Autorización uso de pagaré GA-F13',      'color' => '#ffedd5', 'text' => '#9a3412', 'border' => '#fdba74', 'archivo' => 'GA-F13  Autorización uso de pagaré.pdf'],
            'autorizacion_datos'    => ['label' => 'Autorización datos personales GA-F14',   'color' => '#e0f2fe', 'text' => '#075985', 'border' => '#7dd3fc', 'archivo' => 'GA-F14 Autorización tratamiento de datos personales 2024.pdf'],
            'contrato_servicios'    => ['label' => 'Contrato de servicios',                  'color' => '#f1f5f9', 'text' => '#1e293b', 'border' => '#94a3b8', 'archivo' => 'Contrato de servicios año 2026.pdf'],
        ];
    }
}
