<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/students/{student}/pdf/pre-matricula',  [StudentPdfController::class, 'preMatricula'])->name('students.pdf.pre-matricula');
    Route::get('/students/{student}/pdf/hoja-matricula', [StudentPdfController::class, 'hojaMatricula'])->name('students.pdf.hoja-matricula');
    Route::get('/students/{student}/documentos',                [StudentPdfController::class, 'documentos'])->name('students.documentos');
    Route::post('/students/{student}/documentos/{tipo}/marcar', [StudentPdfController::class, 'marcarDocumento'])->name('students.documentos.marcar');
    Route::get('/students/{student}/documentos-fisicos',                   [StudentPdfController::class, 'documentosFisicos'])->name('students.documentos.fisicos');
    Route::post('/students/{student}/documentos-fisicos/{item}/toggle',    [StudentPdfController::class, 'toggleDocumentoFisico'])->name('students.documentos.fisicos.toggle');
});

require __DIR__.'/auth.php';
