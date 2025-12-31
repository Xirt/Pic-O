<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\EnsureInitialization;
use App\Http\Middleware\OptionalAuth;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\{
    SetupController,
    PhotoController,
    FolderController,
    AlbumController,
    AdminController
};

Route::get('/', function () {

    if (Auth::check()) {
        return redirect('/home');
    }

    return redirect()->route('login');
});

/*
 *--------------------------------------------------------------------------
 * Setup routes (application initialization)
 *--------------------------------------------------------------------------
 */
Route::get('/setup', [SetupController::class, 'show'])->name('setup.show');
Route::post('/setup', [SetupController::class, 'initialize'])->name('setup.init');

/*
 *--------------------------------------------------------------------------
 * Auth routes
 *--------------------------------------------------------------------------
 */
Route::middleware([

    EnsureInitialization::class

])->group(function () {

    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

});

/*
 *--------------------------------------------------------------------------
 * Authenticated user routes
 *--------------------------------------------------------------------------
 */
Route::middleware([

    EnsureInitialization::class,
    OptionalAuth::class,

])->group(function () {

    // Home (Photos index)
    Route::get('/home', [PhotoController::class, 'index'])->name('home');

    // Albums
    Route::prefix('albums')->as('albums.')->group(function () {
        Route::get('/', [AlbumController::class, 'index'])->name('index');
        Route::get('{album}', [AlbumController::class, 'show'])->name('show');
        Route::get('{album}/preview', [AlbumController::class, 'showPreview'])->name('preview');
        Route::get('{album}/cover', [AlbumController::class, 'showCover'])->name('cover');
    });

    // Photos
    Route::prefix('photos')->as('photos.')->group(function () {
        Route::get('/', [PhotoController::class, 'index'])->name('index');
        Route::get('{photo}', [PhotoController::class, 'showRender'])->name('show');
        Route::get('{photo}/download', [PhotoController::class, 'download'])->name('download');
        Route::get('{photo}/thumbnail', [PhotoController::class, 'showThumbnail'])->name('thumbnail');
    });
});

/*
 *--------------------------------------------------------------------------
 * Admin-only routes
 *--------------------------------------------------------------------------
 */
Route::middleware([

    EnsureInitialization::class,
    'auth'

])->group(function ()
{

    // Folders
    Route::prefix('folders')->as('folders.')->group(function ()
    {
        Route::get('/', [FolderController::class, 'index'])->name('index');
        Route::get('{id}', [FolderController::class, 'show'])->name('show');
        Route::get('{id}/thumbnail', [FolderController::class, 'thumbnail'])->name('thumbnail');
    });

    // Admin
    Route::prefix('admin')->as('admin.')->group(function ()
    {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/scanner-log', [AdminController::class, 'getScannerLog'])->name('log');
    });
});

/*
 *--------------------------------------------------------------------------
 * Fallback route (404)
 *--------------------------------------------------------------------------
 */
Route::fallback(function ()
{
    return response()->view('pages.error', [], 404);
});
