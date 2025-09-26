<?php

namespace App\Http\Controllers;
                            
use App\Models\User;

class UserController extends Controller
{
    // GET /users
    public function index()
    {
        $users = User::all();

        return view('pages.users', compact('users'));
    }
}
