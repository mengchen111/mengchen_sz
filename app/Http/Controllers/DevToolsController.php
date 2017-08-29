<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class DevToolsController extends Controller
{
    public function listSession(Request $request)
    {
        return $request->session()->all();
    }

    public function hashedPass($pass)
    {
        return bcrypt($pass);
    }
}