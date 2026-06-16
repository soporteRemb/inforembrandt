<?php

namespace App\Services\Boletines;

use App\Models\Sede;
use RuntimeException;

class BoletinTemplateService
{
    public function base(?Sede $sede = null): string
    {
        return $this->path($sede, 'boletin_hojabase.pdf');
    }

    public function hojaFinal(?Sede $sede = null): string
    {
        return $this->path($sede, 'boletin_hojafinal.pdf');
    }

    private function path(?Sede $sede, string $fileName): string
    {
        $folder = $this->folderForSede($sede);

        $path = resource_path("templates/boletines/{$folder}/{$fileName}");

        if (! file_exists($path)) {
            throw new RuntimeException("No existe la plantilla del boletín: {$path}");
        }

        return $path;
    }

    private function folderForSede(?Sede $sede): string
    {
        $nombre = strtolower(trim($sede?->nombre ?? ''));

        return match (true) {
            str_contains($nombre, 'escuderem') => 'escuderem',
            default => 'rembrandt',
        };
    }
}