<?php

namespace App\Http\Controllers;

use App\Services\Financiero\Pagos\Pdf\ReciboPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReciboPdfController extends Controller
{
    public function original(
        Request $request,
        int $operacionPagoId,
        ReciboPdfService $reciboPdfService,
    ) {
        $usuarioId = auth()->id();

        abort_unless($usuarioId, 403);

        $studentId = (int) $request->integer('student_id');
        $sedeId = (int) $request->integer('sede_id');
        $periodoLectivoId = (int) $request->integer(
            'periodo_lectivo_id'
        );

        abort_if(
            ! $studentId
            || ! $sedeId
            || ! $periodoLectivoId,
            422,
            'La información del recibo está incompleta.'
        );

        try {
            $resultado = $reciboPdfService->generarOriginal(
                operacionPagoId: $operacionPagoId,
                studentId: $studentId,
                sedeId: $sedeId,
                periodoLectivoId: $periodoLectivoId,
                generadoPor: (int) $usuarioId,
            );

            $rutaAbsoluta = Storage::disk('public')->path(
                $resultado['ruta_pdf']
            );

            abort_unless(
                is_file($rutaAbsoluta),
                404,
                'No se encontró el archivo generado.'
            );

            return response()->file(
                $rutaAbsoluta,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' =>
                        'inline; filename="recibo-'
                        . $resultado['identificador']
                        . '.pdf"',
                ]
            );
        } catch (ValidationException $exception) {
            abort(
                422,
                collect($exception->errors())
                    ->flatten()
                    ->first()
                    ?? 'No fue posible generar el recibo.'
            );
        } catch (Throwable $exception) {
            report($exception);

            abort(
                500,
                'Ocurrió un error al generar el recibo.'
            );
        }
    }

    public function reimpresion(
        Request $request,
        int $operacionPagoId,
        ReciboPdfService $reciboPdfService,
    ) {
        $usuarioId = auth()->id();

        abort_unless($usuarioId, 403);

        $studentId = (int) $request->integer('student_id');
        $sedeId = (int) $request->integer('sede_id');
        $periodoLectivoId = (int) $request->integer(
            'periodo_lectivo_id'
        );

        abort_if(
            ! $studentId
            || ! $sedeId
            || ! $periodoLectivoId,
            422,
            'La información del recibo está incompleta.'
        );

        try {
            $resultado = $reciboPdfService->generarReimpresion(
                operacionPagoId: $operacionPagoId,
                studentId: $studentId,
                sedeId: $sedeId,
                periodoLectivoId: $periodoLectivoId,
                generadoPor: (int) $usuarioId,
                motivo: $request->input('motivo'),
            );

            $rutaAbsoluta = Storage::disk('public')->path(
                $resultado['ruta_pdf']
            );

            abort_unless(
                is_file($rutaAbsoluta),
                404,
                'No se encontró el archivo generado.'
            );

            return response()->file(
                $rutaAbsoluta,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' =>
                        'inline; filename="recibo-'
                        . $resultado['identificador']
                        . '.pdf"',
                ]
            );
        } catch (ValidationException $exception) {
            abort(
                422,
                collect($exception->errors())
                    ->flatten()
                    ->first()
                    ?? 'No fue posible generar la reimpresión.'
            );
        } catch (Throwable $exception) {
            report($exception);

            abort(
                500,
                'Ocurrió un error al generar la reimpresión.'
            );
        }
    }
}