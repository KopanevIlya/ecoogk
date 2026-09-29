<?php
use App\Models\Report;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use Inertia\Inertia;

use App\Services\AiVisionService;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/test-ai/{report}', function (Report $report, AiVisionService $service) {
        return response()->json($service->analyzeReport($report));
    });
    
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
});


require __DIR__.'/auth.php';
