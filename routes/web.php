<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BloodRequestController;
use App\Http\Controllers\DonorSearchController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoFileController;
use Illuminate\Support\Facades\Route;

// Public pages
Route::controller(PageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/about', 'about')->name('about');
    Route::get('/gallery', 'gallery')->name('gallery');
    Route::get('/tips', 'tips')->name('tips');
    Route::get('/terms', 'terms')->name('terms');
    Route::get('/privacy', 'privacy')->name('privacy');
});

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/blood-requests', [BloodRequestController::class, 'index'])->name('requests.index');
Route::get('/blood-requests/new', [BloodRequestController::class, 'create'])->name('requests.create');
Route::get('/search-donors', DonorSearchController::class)->name('donors.search');

// Auth
Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::get('/register', 'register')->name('register');
    Route::get('/login', 'login')->name('login');
    Route::get('/forgot-password', 'forgotPassword')->name('password.request');
});

Route::middleware('auth')->group(function () {
    Route::get('/account', [AuthController::class, 'account'])->name('account');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/login', 'pages.admin.login')->middleware('guest')->name('login');

    // auth.session lets a password change sign out the admin's other devices.
    Route::middleware(['auth', 'auth.session', 'admin'])->group(function () {
        Route::view('/', 'pages.admin.dashboard')->name('dashboard');
        Route::view('/donors', 'pages.admin.donors')->name('donors');
        Route::view('/blood-requests', 'pages.admin.requests')->name('requests');
        Route::view('/thanas', 'pages.admin.thanas')->name('thanas');
        Route::view('/blog', 'pages.admin.posts')->name('posts');
        Route::view('/blog/new', 'pages.admin.post-editor')->name('posts.create');
        Route::view('/blog/{post}/edit', 'pages.admin.post-editor')->whereNumber('post')->name('posts.edit');
        Route::view('/gallery', 'pages.admin.gallery')->name('gallery');
        Route::view('/videos', 'pages.admin.videos')->name('videos');
        Route::view('/about', 'pages.admin.about')->name('about');
        Route::view('/settings', 'pages.admin.settings')->name('settings');
        Route::view('/profile', 'pages.admin.profile')->name('profile');
        Route::view('/admins', 'pages.admin.admins')->name('admins');
        Route::post('/logout', [AuthController::class, 'adminLogout'])->name('logout');
    });
});

// Machine-readable files
Route::get('/sitemap.xml', [SeoFileController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoFileController::class, 'robots']);
Route::get('/llms.txt', [SeoFileController::class, 'llms']);
