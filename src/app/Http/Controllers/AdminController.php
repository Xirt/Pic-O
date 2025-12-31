<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use App\Jobs\TraverseFolderJob;
use App\Models\User;
use App\Models\Folder;
use App\Models\Photo;
use App\Models\Album;
use App\Policies\AdminPolicy;

/**
 * Handles administrative pages and actions.
 *
 * Provides:
 *  - Administration dashboard with statistics and access to (user) configuration
 *  - Download of the latest scanner log.
 *
 * Routes:
 *  - GET /admin             -> index()
 *  - GET /admin/scanner-log -> getScannerLog()
 */
class AdminController extends Controller
{
    protected AdminPolicy $policy;

    public function __construct()
    {
        $this->policy = app(AdminPolicy::class);

        $this->middleware(function ($request, $next)
        {
            if (!$this->policy->access($request->user()))
            {
                abort(403);
            }

            return $next($request);
        });
    }

    /**
     * Show the administration page with user and system statistics.
     *
     * @return View
     */
    public function index(): View
    {
        $users = User::all();

        $statistics = [
            'users'             => User::count(),
            'folders'           => Folder::count(),
            'photos'            => Photo::count(),
            'photo_impressions' => Photo::sum('impressions'),
            'photo_downloads'   => Photo::sum('downloads'),
            'albums'            => Album::count(),
            'album_impressions' => Album::sum('impressions'),
        ];

        return view('pages.admin', compact('users', 'statistics'));
    }

    /**
     * Download the latest scanner log file.
     *
     * @return SymfonyResponse
     */
    public function getScannerLog(): SymfonyResponse
    {
        $logPath = storage_path('logs/scanner.log');

        if (!file_exists($logPath))
        {
            return response('', 200)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="scanner.log"');
        }

        return response()->download($logPath, 'scanner.log', [
            'Content-Type' => 'text/plain',
        ]);
    }
}
