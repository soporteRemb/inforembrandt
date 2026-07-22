<?php

namespace App\Services\Institucion;

use App\Models\Sede;

class InstitucionService
{
    public function datos(int $sedeId): array
    {
        $sede = Sede::findOrFail($sedeId);

        return [

            'id' => $sede->id,

            'nombre' => $sede->nombre,

            'codigo' => $sede->codigo,

            'direccion' => $sede->direccion,

            'telefono' => $sede->telefono,

            'email' => $sede->email,

            'nit' => $sede->nit,

            'logo' => $sede->logo,

            'pie_documentos' => $sede->pie_documentos,

            'representante_legal' => $sede->representante_legal,

            'cargo_representante' => $sede->cargo_representante,

            'informacion_legal' => $sede->informacion_legal,

            'prefijo_documentos' => $sede->prefijo_documentos,

        ];
    }
}