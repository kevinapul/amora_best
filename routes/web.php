<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController,
    TrainingController,
    DashboardController,
    AttendanceController,
    EventStaffController,
    EventTrainingController,
    EventParticipantController,
    HRController
};

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('welcome'));

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    /* =======================
     * Dashboard
     * ======================= */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /* =======================
     * Attendance
     * ======================= */
    Route::post('/attendances', [AttendanceController::class, 'store'])
        ->name('attendances.store');

    Route::post('/attendances/checkout', [AttendanceController::class, 'checkout'])
        ->name('attendances.checkout');

    /* =======================
     * Profile
     * ======================= */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    /* =======================
     * Training Master
     * ======================= */
    Route::prefix('training')->name('training.')->group(function () {
        Route::get('/', [TrainingController::class, 'index'])->name('index');
        Route::get('/create', [TrainingController::class, 'create'])->name('create');
        Route::post('/', [TrainingController::class, 'store'])->name('store');
    });

    /* =======================
     * Event Training (CORE)
     * ======================= */
    Route::prefix('event-training')->name('event-training.')->group(function () {

        Route::get('/', [EventTrainingController::class, 'index'])->name('index');
        Route::get('/create', [EventTrainingController::class, 'create'])->name('create');
        Route::post('/', [EventTrainingController::class, 'store'])->name('store');

        Route::get('/{eventTraining}', [EventTrainingController::class, 'show'])
            ->name('show');

        Route::get('/{eventTraining}/edit', [EventTrainingController::class, 'edit'])
            ->name('edit');

        Route::put('/{eventTraining}', [EventTrainingController::class, 'update'])
            ->name('update');

        Route::delete('/{eventTraining}', [EventTrainingController::class, 'destroy'])
            ->name('destroy');

        /* ===== ACC EVENT (ADMIN) ===== */
        Route::post('/{eventTraining}/approve', [EventTrainingController::class, 'approve'])
            ->name('approve');

        /* ===== FINANCE ACC (LAPORAN) ===== */
        Route::post(
            '/{eventTraining}/approve-finance',
            [EventTrainingController::class, 'approveFinance']
        )->name('approveFinance');
    });

    /* =======================
     * Event Participant
     * ======================= */
    Route::prefix('event-training/{event}')
        ->name('event-participant.')
        ->group(function () {

            Route::get('/participant/create', [EventParticipantController::class, 'create'])
                ->name('create');

            Route::post('/participant', [EventParticipantController::class, 'store'])
                ->name('store');

            Route::put('/participant/{participant}', [EventParticipantController::class, 'update'])
                ->name('update');

            Route::delete('/participant/{participant}', [EventParticipantController::class, 'destroy'])
                ->name('destroy');

            /* ===== FINANCE PER PESERTA (REGULER) ===== */
            Route::post(
                '/participant/{participant}/paid',
                [EventParticipantController::class, 'markPaid']
            )->name('markPaid');

            /* ===== ADMINISTRASI SERTIFIKAT ===== */
            Route::post(
                '/participant/{participant}/record-certificate',
                [EventParticipantController::class, 'markCertificateRecorded']
            )->name('recordCertificate');
        });

    /* =======================
     * Event Staff
     * ======================= */
    Route::prefix('event-training/{event}')
        ->name('event-staff.')
        ->group(function () {

            Route::get('/staff', [EventStaffController::class, 'show'])
                ->name('show');

            Route::get('/staff/create', [EventStaffController::class, 'create'])
                ->name('create');

            Route::post('/staff', [EventStaffController::class, 'store'])
                ->name('store');
        });

    Route::delete('/event-staff/{id}', [EventStaffController::class, 'destroy'])
        ->name('event-staff.destroy');

    Route::get('/event-staff/events', [EventStaffController::class, 'eventIndex'])
        ->name('event-staff.events');

    /* =======================
     * Division Pages
     * ======================= */
    Route::view('/division/tools', 'division.tools')->name('division.tools');
    Route::view('/division/ops', 'division.ops')->name('division.ops');

    Route::get('/division/hr', [HRController::class, 'index'])
        ->name('division.hr');

    Route::post('/division/hr/force-checkout/{user}', [HRController::class, 'forceCheckout'])
        ->name('hr.forceCheckout');

    Route::get('/division/training', [EventTrainingController::class, 'certificateDashboard'])
        ->name('division.training');

    /* =======================
     * LAPORAN
     * ======================= */
    Route::get('/laporan', [EventTrainingController::class, 'laporan'])
        ->name('laporan');

    Route::get('/laporan/event', [EventTrainingController::class, 'laporanEvent'])
        ->name('laporan.event');
Route::post(
    '/event/{event}/sync-finance',
    [EventTrainingController::class, 'syncFinance']
)->name('event.sync-finance');
/* =======================
 * EVENT + PESERTA (OVERVIEW)
 * ======================= */
Route::get(
    '/event-training/peserta',
    [EventParticipantController::class, 'index']
)->name('event-training.peserta');

});

require __DIR__.'/auth.php';
