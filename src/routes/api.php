<?php

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Api\{
    AlbumController,
    FolderController,
    PhotoController,
    UserController,
    JobController
};

/*
 *--------------------------------------------------------------------------
 * Authenticated user routes
 *--------------------------------------------------------------------------
 */
Route::middleware([

    EnsureFrontendRequestsAreStateful::class,
    'auth:sanctum',

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

        // Album Photos
        Route::prefix('{album}/photos')->as('photos.')->group(function ()
        {
            Route::get('/', [PhotoController::class, 'byAlbum'])->name('index');
            Route::put('/', [AlbumController::class, 'addPhotos'])->name('addMultiple');
            Route::put('{photo}', [AlbumController::class, 'addPhoto'])->name('addOne');
            Route::delete('{photo}', [AlbumController::class, 'removePhoto'])->name('removeOne');
        });
    });

    // Photos
    Route::prefix('photos')->as('api.photos.')->group(function ()
    {
        Route::get('/', [PhotoController::class, 'index'])->name('index');
        Route::get('{photo}', [PhotoController::class, 'show'])->name('show');
    });
});


/*
 *--------------------------------------------------------------------------
 * Admin-only routes
 *--------------------------------------------------------------------------
 */
Route::middleware([

    EnsureFrontendRequestsAreStateful::class,
    'auth:sanctum',
    AdminMiddleware::class,

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
        Route::get('{user}', [UserController::class, 'show'])->name('show');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('{user}', [UserController::class, 'update'])->name('update');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
    });

});
