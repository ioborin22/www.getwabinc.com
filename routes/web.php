<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EmailCompanyController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// -------------------- Static Pages --------------------

// Home page
Route::get('/', function () {
    return view('index');
});

// About page
Route::get('about', function () {
    return view('about');
});

// Services page
Route::get('services', function () {
    return view('services');
});

// Contact page
Route::get('contact', function () {
    return view('contact');
});

// Privacy Policy page
Route::get('privacy-policy', function () {
    return view('privacy-policy');
});

// Cookie Policy page
Route::get('cookie-policy', function () {
    return view('cookie-policy');
});

// Terms of Use page
Route::get('terms-of-use', function () {
    return view('terms-of-use');
});

// -------------------- End Static Pages --------------------



// -------------------- Login Functionality --------------------

// Logout
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('login');
})->name('logout');

// Login Process
Route::post('login-process', [LoginController::class, 'login'])->name('login-process');

// Show login form (redirects to dashboard if already logged in)
Route::get('login', function () {
    if (Auth::check()) {
        return redirect('dashboard');
    }

    return view('login');
})->name('login');

// -------------------- End Login Functionality --------------------



// -------------------- Authenticated Routes --------------------

// Группа маршрутов, требующих аутентификацию
Route::middleware('auth')->group(function () {
    // Dashboard route
    Route::get('dashboard', function () {
        $companies = DB::table('email_companies')->get();
        return view('dashboard', ['companies' => $companies]);
    })->name('dashboard');

    // Add company form
    Route::get('/add-company', [EmailCompanyController::class, 'create'])
        ->name('add-company');

    // Store new company
    Route::post('/add-company', [EmailCompanyController::class, 'store'])
        ->name('store-company');

    // Get DKIM settings
    Route::get('/dkim', [EmailCompanyController::class, 'getDkim'])
        ->name('dkim');

    Route::get('/logs', [EmailCompanyController::class, 'logs'])->name('logs');
    Route::get('/logs-hello-email', [EmailCompanyController::class, 'showHelloEmailLogs'])->name('show-hello-email-logs');
    Route::get('/logs-again-email', [EmailCompanyController::class, 'showAgainEmailLogs'])->name('show-again-email-logs');
    Route::get('/logs-last-email', [EmailCompanyController::class, 'showLastEmailLogs'])->name('show-last-email-logs');

    // -------------------- Email Views for Sending --------------------

    // Hello Email view
    Route::get('/hello-email', function () {
        $company = new stdClass();
        $company->recipient_name = 'Name';
        $company->recipient_email = 'email@example.com';
        $company->company_name = 'Your Company';
        $company->contract_topic = 'Contract';
        $company->contract_id = '12345';
        $company->contract_start_date = '2024-01-01';
        $company->contract_end_date = '2024-12-31';
        $company->subscribe = 'Yes';
        $company->hello_email = '2024-01-02';
        $company->hello_email_again = '2024-06-01';
        $company->last_email_at = '2024-06-01';
        $company->created_at = '2024-01-01';

        return view('mail.hello_email', ['company' => $company]);
    })->name('hello-email');

    // Hello Again Email view
    Route::get('/hello-again', function () {
        $company = new stdClass();
        $company->recipient_name = 'Name';
        $company->recipient_email = 'email@example.com';
        $company->company_name = 'Your Company';
        $company->contract_topic = 'Contract';
        $company->contract_id = '12345';
        $company->contract_start_date = '2024-01-01';
        $company->contract_end_date = '2024-12-31';
        $company->subscribe = 'Yes';
        $company->hello_email = '2024-01-02';
        $company->hello_email_again = '2024-06-01';
        $company->last_email_at = '2024-06-01';
        $company->created_at = '2024-01-01';

        return view('mail.hello_again', ['company' => $company]);
    })->name('hello-again');

    // Last Email view
    Route::get('/last-email', function () {
        $company = new stdClass();
        $company->recipient_name = 'Name';
        $company->recipient_email = 'email@example.com';
        $company->company_name = 'Your Company';
        $company->contract_topic = 'Contract';
        $company->contract_id = '12345';
        $company->contract_start_date = '2024-01-01';
        $company->contract_end_date = '2024-12-31';
        $company->subscribe = 'Yes';
        $company->hello_email = '2024-01-02';
        $company->hello_email_again = '2024-06-01';
        $company->last_email_at = '2024-06-01';
        $company->created_at = '2024-01-01';

        return view('mail.last_email', ['company' => $company]);
    })->name('last-email');

    // -------------------- End Email Views for Sending --------------------
});

// -------------------- End Authenticated Routes --------------------



// -------------------- Unsubscribe Functionality --------------------

// Unsubscribe route
Route::get('/unsubscribe', [EmailCompanyController::class, 'unsubscribe'])->name('unsubscribe');

// -------------------- End Unsubscribe Functionality --------------------
