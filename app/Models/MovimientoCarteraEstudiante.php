<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoCarteraEstudiante extends Model
{
    protected $table = 'movimientos_cartera_estudiante';

    protected $fillable = [
        'student_id',
        'sede_id',
        'periodo_lectivo_id',
        'course_id',
        'concepto_cobro_id',
        'grado',
        'tipo_movimiento',
        'tipo_concepto',
        'mes',
        'mes_numero',
        'valor_base',
        'valor_personalizado',
        'valor',
        'estado',
        'descripcion',
        'referencia',
        'causado_por',
        'causado_en',
        'reversado_por',
        'reversado_en',
        'motivo_reversion',
        'observacion',
        'documento_referencia',
        'fecha_movimiento',
        'fecha_vencimiento',
        'nota_interna',
    ];

    protected $casts = [
        'valor_base' => 'decimal:2',
        'valor_personalizado' => 'decimal:2',
        'valor' => 'decimal:2',
        'causado_en' => 'datetime',
        'reversado_en' => 'datetime',
        'fecha_movimiento' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function periodoLectivo()
    {
        return $this->belongsTo(PeriodoLectivo::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function conceptoCobro()
    {
        return $this->belongsTo(ConceptoCobro::class);
    }

    public function causadoPor()
    {
        return $this->belongsTo(User::class, 'causado_por');
    }

    public function reversadoPor()
    {
        return $this->belongsTo(User::class, 'reversado_por');
    }

public function aplicacionesPago()
    {
        return $this->hasMany(AplicacionPago::class);
    }

}