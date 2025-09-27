<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\{
    PhotoController,
    FolderController,
    AlbumController,
    UserController
};

Route::get('/', function () {
    return redirect()->route('login');
});

/*
 *--------------------------------------------------------------------------
 * Auth routes
 *--------------------------------------------------------------------------
 */
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


/*
 *--------------------------------------------------------------------------
 * Authenticated user routes
 *--------------------------------------------------------------------------
 */
Route::middleware(['auth'])->group(function () {

    // Home (Photos index)
    Route::get('/home', [PhotoController::class, 'index'])->name('home');

    // Albums
    Route::prefix('albums')->as('albums.')->group(function () {
        Route::get('/', [AlbumController::class, 'index'])->name('index');
        Route::get('{album}', [AlbumController::class, 'show'])->name('show');
        Route::get('{album}/thumbnail', [AlbumController::class, 'showThumbnail'])->name('thumbnail');
    });

    // Photos
    Route::prefix('photos')->as('photos.')->group(function () {
        Route::get('/', [FolderController::class, 'index'])->name('index'); // Note: Maybe this should be AlbumController?
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
Route::middleware(['auth', AdminMiddleware::class])->group(function ()
{

    // Folders
    Route::prefix('folders')->as('folders.')->group(function ()
    {
        Route::get('/', [FolderController::class, 'index'])->name('index');
        Route::get('{id}', [FolderController::class, 'show'])->name('show');
        Route::get('{id}/thumbnail', [FolderController::class, 'thumbnail'])->name('thumbnail');
    });

    // Users
    Route::prefix('users')->as('users.')->group(function ()
    {
        Route::get('/', [UserController::class, 'index'])->name('index');
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
