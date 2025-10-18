<?php

namespace App\Http\Controllers;
use App\Jobs\TraverseFolderJob;
                            
use App\Models\User;
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

        return view('pages.admin', compact('users'));
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
