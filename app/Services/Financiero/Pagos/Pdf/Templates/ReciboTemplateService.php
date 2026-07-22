<?php

namespace App\Services\Financiero\Pagos\Pdf\Templates;

class ReciboTemplateService
{
    public function base(): string
    {
        return resource_path(
            'pdf/recibos/recibo-media-carta-horizontal.pdf'
        );
    }

    /*
     * Preparado para futuras versiones.
     */
    public function carta(): string
    {
        return resource_path(
            'pdf/recibos/recibo-carta.pdf'
        );
    }
}