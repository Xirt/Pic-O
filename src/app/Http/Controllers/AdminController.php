<?php

namespace App\Http\Controllers;
use App\Jobs\TraverseFolderJob;
                            
use App\Models\User;

class AdminController extends Controller
{
    // GET /admin
    public function index()
    {
        $users = User::all();

        return view('pages.admin', compact('users'));
    }
}
