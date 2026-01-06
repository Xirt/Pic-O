<?php

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\OptionalAuth;
use App\Http\Middleware\EnsureInitialization;
use App\Http\Controllers\Api\{
    ShareTokenController,
    SettingController,
    FolderController,
    PhotoController,
    AlbumController,
    UserController,
    JobController
};

/*
 *--------------------------------------------------------------------------
 * Authenticated user routes
 *--------------------------------------------------------------------------
 */
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::middleware([

    EnsureInitialization::class,
    'web',
    OptionalAuth::class,

])->group(function ()
{
    // Albums
    Route::prefix('albums')->as('api.albums.')->group(function ()
    {
        Route::get('/', [AlbumController::class, 'index'])->name('index');
        Route::get('search', [AlbumController::class, 'search'])->name('search');
        Route::get('{album}', [AlbumController::class, 'show'])->name('show');
        Route::post('/', [AlbumController::class, 'store'])->name('store');
        Route::post('from-folder', [AlbumController::class, 'storeFromFolder'])->name('storeFromFolder');
        Route::patch('{album}', [AlbumController::class, 'update'])->name('update');
        Route::delete('{album}', [AlbumController::class, 'destroy'])->name('destroy');
        Route::get('/{album}/tokens', [ShareTokenController::class, 'index'])->name('tokens');

        // Album Photos
        Route::prefix('{album}/photos')->as('photos.')->group(function ()
        {
            Route::get('/', [PhotoController::class, 'byAlbum'])->name('index');
            Route::put('/', [AlbumController::class, 'addPhotos'])->name('addMultiple');
            Route::put('/from-folder', [AlbumController::class, 'addFromFolder'])->name('addFromFolder');
            Route::put('{photo}', [AlbumController::class, 'addPhoto'])->name('addOne');
            Route::delete('/', [AlbumController::class, 'removePhotos'])->name('removeMultiple');
            Route::delete('{photo}', [AlbumController::class, 'removePhoto'])->name('removeOne');
        });
    });

    // Photos
    Route::prefix('photos')->as('api.photos.')->group(function ()
    {
        Route::get('/', [PhotoController::class, 'index'])->name('index');
        Route::get('{photo}', [PhotoController::class, 'show'])->name('show');
        Route::post('{photo}/impression', [PhotoController::class, 'recordImpression'])->name('impression');
    });

    // Tokens
    Route::prefix('tokens')->as('api.tokens.')->group(function ()
    {
        Route::post('/', [ShareTokenController::class, 'store'])->name('store');
        Route::delete('{token}', [ShareTokenController::class, 'destroy'])->name('destroy');
    });

    // Users
    Route::prefix('users')->as('api.users.')->group(function ()
    {
        Route::get('{user}', [UserController::class, 'show'])->name('show');
        Route::put('{user}', [UserController::class, 'update'])->name('update');
    });

});


/*
 *--------------------------------------------------------------------------
 * Admin-only routes
 *--------------------------------------------------------------------------
 */
Route::middleware([

    EnsureInitialization::class,
    'web',
    'auth:sanctum',

])->group(function ()
{
    // Jobs
    Route::prefix('jobs')->as('api.jobs.')->group(function ()
    {
        Route::get('/', [JobController::class, 'index'])->name('index');
        Route::post('dispatch', [JobController::class, 'dispatchJob'])->name('dispatch');
        Route::get('pending-count', [JobController::class, 'countPending'])->name('count');
    });

    // Folders
    Route::prefix('folders')->as('api.folders.')->group(function ()
    {
        Route::get('/', [FolderController::class, 'index'])->name('index');
        Route::get('search', [FolderController::class, 'search'])->name('search');
        Route::get('{folder}', [FolderController::class, 'show'])->name('show');
        Route::get('{folder}/subfolders', [FolderController::class, 'subfolders'])->name('subfolders');
        Route::get('{folder}/photos', [PhotoController::class, 'byFolder'])->name('photos');
    });

    // Users
    Route::prefix('users')->as('api.users.')->group(function ()
    {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Settings
    Route::prefix('settings')->as('api.settings.')->group(function ()
    {
        Route::post('/', [SettingController::class, 'store'])->name('store');
    });
});
