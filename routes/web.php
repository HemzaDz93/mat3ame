<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\CardsController;
use App\Http\Controllers\CanteenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MealForecastController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TraineeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| المصادقة
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

    // 5 محاولات دخول كحد أقصى كل 15 دقيقة لكل عنوان IP.
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,15')
        ->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| المسارات المحمية (تسجيل الدخول + حساب نشط)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /* ------------------------- الحساب ------------------------- */
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::post('/account', [AccountController::class, 'update'])->name('account.update');

    /* ------------------------- إدارة المستخدمين (مدير) ------------------------- */
    Route::middleware('role:admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/new', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::post('/{user}/delete', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/toggle', [UserController::class, 'toggle'])->name('toggle');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
    });

    /* ------------------------- المتربصون ------------------------- */
    Route::middleware('role:admin,supervisor,staff')->group(function () {
        Route::post('/trainees/bulk-delete', [TraineeController::class, 'bulkDelete'])->name('trainees.bulk-delete');
        Route::post('/trainees/bulk-restore', [TraineeController::class, 'bulkRestore'])->name('trainees.bulk-restore');
        Route::post('/trainees/{trainee}/delete', [TraineeController::class, 'destroy'])->name('trainees.destroy');
        Route::post('/trainees/{trainee}/restore', [TraineeController::class, 'restore'])->name('trainees.restore');
        Route::get('/trainees/{trainee}/photo', [TraineeController::class, 'photo'])->name('trainees.photo');
        Route::get('/trainees/{trainee}/documents/{document}', [DocumentController::class, 'download'])->name('documents.download');
        Route::post('/trainees/{trainee}/documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::post('/documents/{document}/delete', [DocumentController::class, 'destroy'])->name('documents.destroy');

        Route::get('/trainees', [TraineeController::class, 'index'])->name('trainees.index');
        Route::get('/trainees/new', [TraineeController::class, 'create'])->name('trainees.create');
        Route::post('/trainees', [TraineeController::class, 'store'])->name('trainees.store');
        Route::get('/trainees/{trainee}', [TraineeController::class, 'show'])->name('trainees.show');
        Route::get('/trainees/{trainee}/edit', [TraineeController::class, 'edit'])->name('trainees.edit');
        Route::put('/trainees/{trainee}', [TraineeController::class, 'update'])->name('trainees.update');
    });

    /* ------------------------- الموظفون ------------------------- */
    Route::middleware('role:admin,supervisor,staff')->prefix('employees')->name('employees.')->group(function () {
        Route::get('/cards', [CardsController::class, 'employees'])->name('cards');
        Route::post('/cards/print', [CardsController::class, 'employeesPrint'])->name('cards.print');
        Route::post('/{employee}/delete', [EmployeeController::class, 'destroy'])->name('destroy');
        Route::post('/{employee}/restore', [EmployeeController::class, 'restore'])->name('restore');
        Route::get('/{employee}/photo', [EmployeeController::class, 'photo'])->name('photo');
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/new', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
    });

    /* ------------------------- البطاقات والقوائم ------------------------- */
    Route::middleware('role:admin,supervisor,staff')->group(function () {
        Route::get('/cards', [CardsController::class, 'index'])->name('cards.index');
        Route::post('/cards/print', [CardsController::class, 'print'])->name('cards.print');
        Route::get('/roster', [RosterController::class, 'index'])->name('roster');
        Route::get('/roster/export/{format}', [RosterController::class, 'export'])->name('roster.export');
    });

    /* ------------------------- السداد ------------------------- */
    Route::middleware('role:admin,supervisor,staff')->prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/new', [PaymentController::class, 'create'])->name('create');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::post('/direct', [PaymentController::class, 'storeDirect'])->name('store-direct');
        Route::post('/decide', [PaymentController::class, 'decide'])->name('decide');

        // الانتهاء القريب والتجديد الجماعي
        Route::get('/renewals', [PaymentController::class, 'renewals'])->name('renewals');
        Route::post('/renewals', [PaymentController::class, 'renewBulk'])->name('renew-bulk');

        // قائمة المتأخرات (الديون)
        Route::get('/debts', [PaymentController::class, 'debts'])->name('debts');

        // وصولات السداد القابلة للطباعة
        Route::get('/employee/{payment}/receipt', [PaymentController::class, 'receiptEmployee'])->name('employees.receipt');
        Route::get('/{payment}/receipt', [PaymentController::class, 'receipt'])->name('receipt');

        // سدادات الموظفين (جدول مستقل)
        Route::post('/employee', [PaymentController::class, 'storeEmployee'])->name('employees.store');
        Route::post('/employee/direct', [PaymentController::class, 'storeEmployeeDirect'])->name('employees.store-direct');
        Route::post('/employee/decide', [PaymentController::class, 'decideEmployee'])->name('employees.decide');
    });

    /* ------------------------- المطعم ------------------------- */
    Route::middleware('role:admin,supervisor,staff,canteen')->prefix('canteen')->name('canteen.')->group(function () {
        Route::get('/', [CanteenController::class, 'index'])->name('index');
        Route::get('/find', [CanteenController::class, 'find'])->name('find');
        Route::post('/serve/{trainee}', [CanteenController::class, 'serve'])->name('serve');
        Route::post('/employee/serve/{employee}', [CanteenController::class, 'serveEmployee'])->name('serve-employee');
        Route::post('/pay/{trainee}', [CanteenController::class, 'pay'])->name('pay');
        Route::get('/t/{code}', [CanteenController::class, 'show'])->name('show');
    });

    Route::middleware('role:admin,supervisor')->group(function () {
        Route::get('/canteen/report', [CanteenController::class, 'report'])->name('canteen.report');
        Route::get('/canteen/report/export/{format}', [CanteenController::class, 'reportExport'])->name('canteen.report.export');
    });

    /* ------------------------- إعلان الوجبات (مساعد التكوين) ------------------------- */
    Route::middleware('role:admin,assistant')->prefix('forecast')->name('forecast.')->group(function () {
        Route::get('/', [MealForecastController::class, 'index'])->name('index');
        Route::post('/', [MealForecastController::class, 'store'])->name('store');
        Route::post('/{forecast}/delete', [MealForecastController::class, 'destroy'])->name('destroy');
    });

    /* ------------------------- الاستيراد والتصدير ------------------------- */
    Route::middleware('role:admin,staff')->group(function () {
        Route::get('/import', [ImportController::class, 'form'])->name('import.form');
        Route::post('/import/preview', [ImportController::class, 'preview'])->name('import.preview');
        Route::post('/import', [ImportController::class, 'store'])->name('import.store');
        Route::get('/photos', [PhotoController::class, 'form'])->name('photos.form');
        Route::post('/photos', [PhotoController::class, 'store'])->name('photos.store');
    });

    Route::middleware('role:admin,supervisor,staff')->group(function () {
        Route::get('/export/trainees/{format}', [ExportController::class, 'trainees'])->name('export.trainees');
        Route::get('/export/payments/{format}', [ExportController::class, 'payments'])->name('export.payments');
        Route::get('/export/employee-payments/{format}', [ExportController::class, 'employeePayments'])->name('export.employee-payments');
        Route::get('/export/payment-debts/{format}', [ExportController::class, 'paymentDebts'])->name('export.payment-debts');
    });

    /* ------------------------- الإعدادات (مدير) ------------------------- */
    Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'edit'])->name('edit');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
    });

    /* ------------------------- الإشعارات ------------------------- */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/read-all', [NotificationController::class, 'readAll'])->name('read-all');
        Route::post('/{notification}/read', [NotificationController::class, 'read'])->name('read');
    });

    /* ------------------------- سجل العمليات (مدير) ------------------------- */
    Route::middleware('role:admin')->get('/logs', [LogController::class, 'index'])->name('logs.index');

    /* ------------------------- النسخ الاحتياطي (مدير) ------------------------- */
    Route::middleware('role:admin')->prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/', [BackupController::class, 'store'])->name('store');
        Route::get('/{backup}/download', [BackupController::class, 'download'])->name('download');
        Route::post('/{backup}/delete', [BackupController::class, 'destroy'])->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| مصادر الرموز (متاحة للمسجلين فقط)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/settings/logo', [SettingsController::class, 'logo'])->name('settings.logo');
    Route::get('/barcode/{code}', [BarcodeController::class, 'code128'])->name('barcode.code128');
    Route::get('/qrcode/{code}', [BarcodeController::class, 'qr'])->name('barcode.qr');
});
