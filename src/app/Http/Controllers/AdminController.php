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

    // GET /admin
    public function index()
    {
        $users = User::all();

        return view('pages.admin', compact('users'));
    }
}
