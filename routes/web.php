<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\{
    ProfileController,
    DashboardController,
    AttendanceController,
    EventStaffController,
    EventTrainingController,
    EventParticipantController,
    HRController,
    MasterTrainingController,
    EventTrainingGroupController,
    InvoiceController,
    FinanceGroupController,
};

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/', fn () => redirect()->route('dashboard'));

    /* =======================
     * DASHBOARD
     * ======================= */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /* =======================
     * ATTENDANCE
     * ======================= */
    Route::post('/attendances', [AttendanceController::class, 'store'])
        ->name('attendances.store');

    Route::post('/attendances/checkout', [AttendanceController::class, 'checkout'])
        ->name('attendances.checkout');

    /* =======================
     * PROFILE
     * ======================= */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    /* ==========================================================
     * MASTER TRAINING
     * ========================================================== */
    Route::prefix('training')->name('master-training.')->group(function () {
        Route::get('/', [MasterTrainingController::class, 'index'])->name('index');
        Route::get('/create', [MasterTrainingController::class, 'create'])->name('create');
        Route::post('/', [MasterTrainingController::class, 'store'])->name('store');
        Route::get('/{masterTraining}', [MasterTrainingController::class, 'show'])->name('show');

        Route::post(
            '/event-training-group/{group}/approve',
            [EventTrainingGroupController::class, 'approve']
        )->name('event-training-group.approve');
    });

    /* ==========================================================
     * EVENT TRAINING (CORE)
     * ========================================================== */
    Route::prefix('event-training')->name('event-training.')->group(function () {

        // 🔹 GROUP DETAIL (PRIMARY)
        Route::get('/group/{group}', [EventTrainingGroupController::class, 'show'])
            ->name('group.show');

        Route::get('/', [EventTrainingController::class, 'index'])->name('index');
        Route::get('/create', [EventTrainingController::class, 'create'])->name('create');
        Route::post('/', [EventTrainingController::class, 'store'])->name('store');

        // 🔹 EVENT DETAIL (READ ONLY)
        Route::get('/{eventTraining}', [EventTrainingController::class, 'show'])
            ->name('show');

        Route::get('/{eventTraining}/edit', [EventTrainingController::class, 'edit'])
            ->name('edit');

        Route::put('/{eventTraining}', [EventTrainingController::class, 'update'])
            ->name('update');

        Route::delete('/{eventTraining}', [EventTrainingController::class, 'destroy'])
            ->name('destroy');

        Route::post('/{eventTraining}/approve', [EventTrainingController::class, 'approve'])
            ->name('approve');

        Route::post(
            '/{eventTraining}/approve-finance',
            [EventTrainingController::class, 'approveFinance']
        )->name('approveFinance');
    });

    /* ==========================================================
     * EVENT PARTICIPANT
     * ========================================================== */
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

            // 🟡 LEGACY — DO NOT USE (Invoice replaces this)
            Route::post(
                '/participant/{participant}/paid',
                [EventParticipantController::class, 'markPaid']
            )->name('markPaid');
        });

    /* ==========================================================
     * LEGACY FINANCE EVENT (DO NOT USE)
     * ========================================================== */
    Route::get(
        '/event-training/{eventTraining}/finance',
        [EventTrainingController::class, 'finance']
    )->name('event-training.finance');

    Route::post(
        '/event-training/{eventTraining}/bulk-payment',
        [EventTrainingController::class, 'bulkPayment']
    )->name('event-training.bulk-payment');

    /* ==========================================================
     * EVENT STAFF
     * ========================================================== */
    Route::prefix('event-training/{event}')
        ->name('event-staff.')
        ->group(function () {

            Route::get('/staff', [EventStaffController::class, 'show'])->name('show');
            Route::get('/staff/create', [EventStaffController::class, 'create'])->name('create');
            Route::post('/staff', [EventStaffController::class, 'store'])->name('store');
        });

    Route::delete('/event-staff/{id}', [EventStaffController::class, 'destroy'])
        ->name('event-staff.destroy');

    Route::get('/event-staff/events', [EventStaffController::class, 'eventIndex'])
        ->name('event-staff.events');

    /* ==========================================================
     * DIVISION
     * ========================================================== */
    Route::view('/division/tools', 'division.tools')->name('division.tools');
    Route::view('/division/ops', 'division.ops')->name('division.ops');

    Route::get('/division/hr', [HRController::class, 'index'])
        ->name('division.hr');

    Route::post('/division/hr/force-checkout/{user}', [HRController::class, 'forceCheckout'])
        ->name('hr.forceCheckout');

    Route::get('/division/training', [EventTrainingController::class, 'certificateDashboard'])
        ->name('division.training');

    /* ==========================================================
     * LAPORAN
     * ========================================================== */
    Route::get('/laporan', [EventTrainingController::class, 'laporan'])
        ->name('laporan');

    /* ==========================================================
     * OVERVIEW PESERTA
     * ========================================================== */
    Route::get(
        '/event-training/peserta',
        [EventParticipantController::class, 'index']
    )->name('event-training.peserta');
});

/* ==========================================================
 * TEST ONLY
 * ========================================================== */
Route::get('/test-invoice', function () {
    $request = new Request([
        'company_id' => 1,
        'master_training_id' => 1,
    ]);

    return app(InvoiceController::class)->store($request);
})->withoutMiddleware([\App\Http\Middleware\Authenticate::class]);

/* ==========================================================
 * FINANCE (PRIMARY)
 * ========================================================== */
Route::prefix('finance')->name('finance.')->group(function () {

    Route::get('/group/{group}', [FinanceGroupController::class, 'show'])
        ->name('group.show');

    Route::post('/group/{group}/invoice', [FinanceGroupController::class, 'openInvoice'])
        ->name('group.invoice');

    Route::post('/invoice/{invoice}/pay', [FinanceGroupController::class, 'pay'])
        ->name('invoice.pay');
});

Route::get(
    '/invoice/{invoice}',
    [InvoiceController::class, 'show']
)->name('invoice.show');


require __DIR__ . '/auth.php';
