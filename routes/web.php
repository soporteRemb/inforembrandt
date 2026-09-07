<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentPdfController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CostosEstudianteController;
use App\Http\Controllers\ReciboPdfController;
use App\Http\Controllers\PreMatriculaDocumentoController;
use App\Http\Controllers\DocumentoEstudianteController;

Route::redirect('/', '/admin/login');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');


    Route::get(
        '/students/{student}/pdf/pre-matricula',
        [StudentPdfController::class, 'preMatricula']
    )->name('students.pdf.pre-matricula');

    Route::get(
        '/students/{student}/pdf/hoja-matricula',
        [StudentPdfController::class, 'hojaMatricula']
    )->name('students.pdf.hoja-matricula');

    Route::get(
        '/students/{student}/pdf/formato/{tipo}',
        [StudentPdfController::class, 'formato']
    )->name('students.pdf.formato');

    Route::get(
        '/students/{student}/documentos',
        [StudentPdfController::class, 'documentos']
    )->name('students.documentos');

    Route::post(
        '/students/{student}/documentos/{tipo}/toggle',
        [StudentPdfController::class, 'toggleDocumento']
    )->name('students.documentos.toggle');

    Route::get(
        '/students/{student}/documentos-fisicos',
        [StudentPdfController::class, 'documentosFisicos']
    )->name('students.documentos.fisicos');



    /*
    |--------------------------------------------------------------------------
    | Documentos privados de pre-matrícula
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/pre-matriculas/documentos/{documento}/ver',
        [
            PreMatriculaDocumentoController::class,
            'ver',
        ]
    )->name('pre-matriculas.documentos.ver');

    /*
    |--------------------------------------------------------------------------
    | Documentos privados del expediente del estudiante
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/estudiantes/documentos/{documento}/ver',
        [
            DocumentoEstudianteController::class,
            'ver',
        ]
    )->name('estudiantes.documentos.archivos.ver');

    Route::post(
        '/estudiantes/{student}/documentos/adjuntar',
        [
            DocumentoEstudianteController::class,
            'adjuntar',
        ]
    )->name('estudiantes.documentos.archivos.adjuntar');

    Route::delete(
        '/estudiantes/{student}/documentos/{documento}',
        [
            DocumentoEstudianteController::class,
            'quitar',
        ]
    )->name('estudiantes.documentos.archivos.quitar');
});

Route::get(
    '/admin/estudiantes/{student}/costos',
    [CostosEstudianteController::class, 'index']
)->name('costos.estudiante');

Route::post(
    '/admin/estudiantes/{student}/costos',
    [CostosEstudianteController::class, 'guardar']
)->name('costos.estudiante.guardar');

Route::post(
    '/admin/estudiantes/{student}/costos/asignar',
    [CostosEstudianteController::class, 'asignarCostos']
)->name('costos.estudiante.asignar');

Route::post(
    '/pagos/recibos/{operacionPagoId}/original',
    [ReciboPdfController::class, 'original']
)->name('pagos.recibos.original');

Route::post(
    '/pagos/recibos/{operacionPagoId}/reimpresion',
    [ReciboPdfController::class, 'reimpresion']
)->name('pagos.recibos.reimpresion');

require __DIR__.'/auth.php';
