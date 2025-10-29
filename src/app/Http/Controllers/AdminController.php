<?php

namespace App\Http\Controllers;
use App\Jobs\TraverseFolderJob;

use App\Models\User;
use App\Models\Folder;
use App\Models\Photo;
use App\Models\Album;
use App\Policies\AdminPolicy;

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
     * Show administration page
     * GET /admin
     */
    public function index()
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

    public function getScannerLog()
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
