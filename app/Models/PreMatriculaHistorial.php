<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class PreMatriculaHistorial extends Model
{
    protected $table = 'pre_matricula_historiales';

    public $timestamps = false;

    protected $fillable = [
        'pre_matricula_id',
        'user_id',
        'accion',
        'descripcion',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function preMatricula(): BelongsTo
    {
        return $this->belongsTo(
            PreMatricula::class,
            'pre_matricula_id'
        );
    }
}